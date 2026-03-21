<?php

namespace App\Http\Controllers;

use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\User;
use App\Models\Wrestler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DevImportController extends Controller
{
    /**
     * Dev-only SQL dump used to seed users/wrestlers for local testing.
     * Note: This is not executed as SQL; we parse the INSERT value rows and map into current schema.
     */
    private string $sqlDumpPath = 'C:\\xampp\\htdocs\\dev\\importwrestlerandusers.sql';

    private function getCachedSqlRows(string $table): array
    {
        $cacheDir = storage_path('dev_import_cache');
        $metaFile = $cacheDir . DIRECTORY_SEPARATOR . 'meta.json';
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $table . '.json';

        $dumpMtime = file_exists($this->sqlDumpPath) ? filemtime($this->sqlDumpPath) : null;

        if ($dumpMtime !== null && file_exists($metaFile) && file_exists($cacheFile)) {
            $meta = json_decode((string) file_get_contents($metaFile), true);
            if (is_array($meta) && isset($meta['mtime']) && (int) $meta['mtime'] === (int) $dumpMtime) {
                $decoded = json_decode((string) file_get_contents($cacheFile), true);
                return is_array($decoded) ? $decoded : [];
            }
        }

        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $rows = $this->parseSqlInsertRows($this->sqlDumpPath, $table);
        file_put_contents($cacheFile, json_encode($rows, JSON_UNESCAPED_SLASHES));
        file_put_contents($metaFile, json_encode(['mtime' => $dumpMtime], JSON_UNESCAPED_SLASHES));

        return $rows;
    }

    /**
     * Minimal heuristic: if first name is in this list we treat the wrestler as a girl.
     * Extend this list if your dump contains different naming patterns.
     */
    private array $girlFirstNames = [
        'anna',
        'annabelle',
        'amy',
        'beth',
        'carmie',
        'cassie',
        'chandra',
        'chloe',
        'connie',
        'courtney',
        'crissy',
        'ella',
        'elizabeth',
        'emily',
        'emma',
        'felicia',
        'grace',
        'hannah',
        'jennifer',
        'june',
        'kayla',
        'lacey',
        'lara',
        'lisa',
        'madeline',
        'maria',
        'megan',
        'melissa',
        'michelle',
        'miryah',
        'nancy',
        'natalie',
        'rachel',
        'rebecca',
        'riley',
        'roxie',
        'sasha',
        'sara',
        'sarah',
        'sophia',
        'stefanie',
        'tandy',
        'victoria',
        'zoe',
        'zara',
        'zoey',
        'zoraya',
    ];

    public function importUsers(Request $request)
    {
        if (! $this->requireAdmin($request)) {
            return $this->abort403();
        }

        $tid = $this->getTournamentIdFromRequest($request);
        if ($tid === null) {
            return $this->selectTournament($request, route('dev.import.users'));
        }

        $tournament = Tournament::findOrFail($tid);
        $rows = $this->getCachedSqlRows('users');

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $email = (string) ($row['email'] ?? '');
            if ($id <= 0 || $email === '') {
                continue;
            }

            if (User::where('id', $id)->exists() || User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $user = new User();
            $user->id = $id;
            $user->name = (string) ($row['name'] ?? '');
            $user->last_name = (string) ($row['last_name'] ?? '');
            $user->phone_number = (string) ($row['phone_number'] ?? '');
            $user->email = $email;
            $user->password = (string) ($row['password'] ?? '');
            $user->accesslevel = (string) ($row['accesslevel'] ?? '10');
            $user->active = (string) ($row['active'] ?? '0');
            $user->username = (string) ($row['username'] ?? '');
            $user->mycode = (string) ($row['mycode'] ?? '');
            $user->Tournament_id = $tournament->id;

            $user->save();
            $inserted++;
        }

        // Optional quality-of-life: attach imported users to the tournament for backend access.
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $user = User::where('id', $id)->first();
            if (! $user) {
                continue;
            }
            if (! $this->tournamentHasUser($tournament->id, $user->id)) {
                $tournament->users()->attach($user->id);
            }
        }

        return redirect()
            ->back()
            ->with('success', "Import users complete. Inserted: {$inserted}, Skipped: {$skipped}.");
    }

    public function importWrestlers(Request $request)
    {
        if (! $this->requireAdmin($request)) {
            return $this->abort403();
        }

        $tid = $this->getTournamentIdFromRequest($request);
        if ($tid === null) {
            return $this->selectTournament($request, route('dev.import.wrestler'));
        }

        $tournament = Tournament::findOrFail($tid);
        $rows = $this->getCachedSqlRows('wrestlers');

        $insertedWrestlers = 0;
        $updatedWrestlers = 0;
        $skippedWrestlers = 0;
        $missingUsers = 0;
        $groupMismatches = 0;
        $createdTournamentWrestlers = 0;
        $skippedTournamentWrestlers = 0;

        $tournamentDate = $tournament->TournamentDate instanceof Carbon
            ? $tournament->TournamentDate
            : Carbon::parse((string) $tournament->TournamentDate);

        foreach ($rows as $row) {
            $wrestlerId = (int) ($row['id'] ?? 0);
            $firstName = trim((string) ($row['wr_first_name'] ?? ''));
            $lastName = trim((string) ($row['wr_last_name'] ?? ''));
            $club = (string) ($row['wr_club'] ?? '');
            $age = (int) ($row['wr_age'] ?? 0);
            $gradeRaw = (string) ($row['wr_grade'] ?? '');
            $weight = $row['wr_weight'] === null ? null : (int) $row['wr_weight'];
            $years = (int) ($row['wr_years'] ?? 0);
            $wins = (int) ($row['wr_wins'] ?? 0);
            $losses = (int) ($row['wr_losses'] ?? 0);
            $usawRaw = $row['usawnumber'] ?? null;
            $usaw = null;
            if ($usawRaw !== null && $usawRaw !== '') {
                $parsedUsaw = (int) $usawRaw;
                // wrestlers.usawnumber is UNSIGNED INT in schema.
                if ($parsedUsaw >= 0 && $parsedUsaw <= 4294967295) {
                    $usaw = $parsedUsaw;
                }
            }
            $coachName = (string) ($row['coach_name'] ?? '');
            $coachPhone = (string) ($row['coach_phone'] ?? '');
            $dumpUserId = (int) ($row['user_id'] ?? 0);

            if ($wrestlerId <= 0 || $firstName === '' || $lastName === '' || $age <= 0) {
                $skippedWrestlers++;
                continue;
            }

            $user = User::where('id', $dumpUserId)->first();
            if (! $user) {
                $missingUsers++;
                continue;
            }

            $gender = $this->guessGenderByFirstName($firstName);
            $dob = $tournamentDate->copy()->subYears($age)->format('Y-m-d');
            $gradeNumeric = $this->gradeToNumber($gradeRaw);

            $wrestler = Wrestler::where('id', $wrestlerId)->first();
            if (! $wrestler) {
                $wrestler = new Wrestler();
                $wrestler->id = $wrestlerId;
                $wrestler->user_id = $user->id;
                $wrestler->wr_first_name = $firstName;
                $wrestler->wr_last_name = $lastName;
                $wrestler->wr_club = $club;
                $wrestler->wr_age = $age;
                $wrestler->wr_grade = $gradeRaw;
                $wrestler->wr_weight = $weight;
                $wrestler->wr_pr = (int) ($row['wr_pr'] ?? $weight ?? 0);
                $wrestler->wr_dob = $dob;
                $wrestler->wr_wins = $wins;
                $wrestler->wr_losses = $losses;
                $wrestler->wr_years = $years;
                $wrestler->usawnumber = $usaw;
                $wrestler->coach_name = $coachName;
                $wrestler->coach_phone = $coachPhone;
                $wrestler->wr_gender = $gender;
                $wrestler->save();
                $insertedWrestlers++;
            } else {
                // Keep import deterministic: if gender/dob are missing, overwrite them.
                $needsUpdate = false;
                if ($wrestler->wr_gender !== $gender) {
                    $wrestler->wr_gender = $gender;
                    $needsUpdate = true;
                }
                if (! $wrestler->wr_dob || $wrestler->wr_age !== $age) {
                    $wrestler->wr_age = $age;
                    $wrestler->wr_dob = $dob;
                    $needsUpdate = true;
                }
                if ($needsUpdate) {
                    $wrestler->save();
                    $updatedWrestlers++;
                }
            }

            $wrestlerGender = $wrestler->wr_gender === 'Girl' ? 'girls' : 'boys';
            $groupGenders = $wrestlerGender === 'girls' ? ['girls', 'coed'] : ['boys', 'coed'];

            $group = DivGroup::where('Tournament_Id', $tid)
                ->whereIn('gender', $groupGenders)
                ->where('MinAge', '<=', $age)
                ->where('MaxAge', '>=', $age)
                ->where('MinGrade', '<=', $gradeNumeric)
                ->where('MaxGrade', '>=', $gradeNumeric)
                ->first();

            if (! $group) {
                $groupMismatches++;
                continue;
            }

            $alreadyInTournament = TournamentWrestler::where('Tournament_id', $tid)
                ->where('Wrestler_Id', $wrestler->id)
                ->exists();

            if ($alreadyInTournament) {
                $skippedTournamentWrestlers++;
                continue;
            }

            TournamentWrestler::create([
                'wr_first_name' => $wrestler->wr_first_name,
                'wr_last_name' => $wrestler->wr_last_name,
                'wr_club' => $wrestler->wr_club,
                'wr_age' => $age,
                'wr_grade' => $wrestler->wr_grade,
                'wr_weight' => $weight,
                'wr_years' => $years,
                'wr_pr' => (int) ($weight ?? 0),
                'wr_dob' => $dob,
                'wr_wins' => $wins,
                'wr_losses' => $losses,
                'group_id' => $group->id,
                'division_id' => $group->Division_id,
                'Wrestler_Id' => $wrestler->id,
                'Tournament_id' => $tid,
                'bracketed' => 0,
                'checked_in' => false,
            ]);

            $createdTournamentWrestlers++;
        }

        return redirect()
            ->back()
            ->with('success', implode(' | ', [
                "Import wrestlers complete.",
                "Inserted wrestlers: {$insertedWrestlers}",
                "Updated wrestlers: {$updatedWrestlers}",
                "Skipped wrestlers: {$skippedWrestlers}",
                "Missing users (skipped): {$missingUsers}",
                "Group mismatches: {$groupMismatches}",
                "Tournament entries created: {$createdTournamentWrestlers}",
                "Tournament entries skipped: {$skippedTournamentWrestlers}",
            ]));
    }

    private function requireAdmin(Request $request): bool
    {
        $user = $request->user();
        return $user && $user->isAdmin();
    }

    private function abort403()
    {
        abort(403, 'Dev import is restricted to administrators.');
    }

    private function getTournamentIdFromRequest(Request $request): ?int
    {
        $tid = $request->query('tid');
        if ($tid === null || $tid === '') {
            return null;
        }
        $tid = (int) $tid;
        return $tid > 0 ? $tid : null;
    }

    private function selectTournament(Request $request, string $importRouteBase)
    {
        $user = $request->user();
        $tournaments = $user->isAdmin()
            ? Tournament::orderBy('TournamentDate', 'desc')->get()
            : $user->managedTournaments()->orderBy('TournamentDate', 'desc')->get();

        return view('dev.import.select-tournament', [
            'tournaments' => $tournaments,
            'selectedTid' => (int) ($request->query('tid') ?? 0),
            'importRouteBase' => $importRouteBase,
        ]);
    }

    private function tournamentHasUser(int $tid, int $userId): bool
    {
        return \DB::table('tournamentusers')
            ->where('Tournament_id', $tid)
            ->where('User_id', $userId)
            ->exists();
    }

    private function guessGenderByFirstName(string $firstName): string
    {
        $normalized = strtolower(trim($firstName));
        $normalized = preg_replace('/[^a-z]/', '', $normalized) ?? '';
        if ($normalized === '') {
            return 'Boy';
        }

        $normalizedGirls = array_map(static fn ($n) => preg_replace('/[^a-z]/', '', strtolower(trim($n))) ?? '', $this->girlFirstNames);
        return in_array($normalized, $normalizedGirls, true) ? 'Girl' : 'Boy';
    }

    private function gradeToNumber(string $grade): int
    {
        $g = strtoupper(trim($grade));
        if ($g === '' || $g === 'NULL') {
            return -1;
        }
        if (in_array($g, ['K', '0'], true)) {
            return 0;
        }
        if (in_array($g, ['PK', 'PRE-K', 'PREK'], true)) {
            return -1;
        }
        if (preg_match('/^-?\\d+$/', $g)) {
            return (int) $g;
        }
        return -1;
    }

    /**
     * Parse INSERT value tuples for a single table out of the phpMyAdmin dump.
     * Returns: array<array<string,mixed>> where keys match the column names in the INSERT.
     */
    private function parseSqlInsertRows(string $sqlPath, string $table): array
    {
        if (! file_exists($sqlPath)) {
            abort(500, "Dev import SQL dump not found: {$sqlPath}");
        }

        $sql = file_get_contents($sqlPath);
        if ($sql === false) {
            abort(500, 'Unable to read dev import SQL dump.');
        }

        $pattern = "/INSERT INTO `{$table}` \\((.*?)\\) VALUES\\s*(.*?);/s";
        if (! preg_match_all($pattern, $sql, $stmts, PREG_SET_ORDER)) {
            return [];
        }

        $rows = [];
        foreach ($stmts as $stmt) {
            $colsRaw = $stmt[1];
            $valuesRaw = $stmt[2];

            $cols = array_map(static function ($c) {
                $c = trim($c);
                $c = trim($c, '`');
                return $c;
            }, explode(',', $colsRaw));

            $tuples = $this->parseSqlValueTuples($valuesRaw);
            foreach ($tuples as $tuple) {
                if (count($tuple) !== count($cols)) {
                    continue;
                }
                $row = [];
                foreach ($cols as $i => $col) {
                    $row[$col] = $tuple[$i];
                }
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Parse `(v1, v2, ...)` tuples from the VALUES section of a MySQL INSERT.
     * Handles quoted strings with escaped single quotes `''`.
     *
     * @return array<int, array<int, mixed>>
     */
    private function parseSqlValueTuples(string $valuesSql): array
    {
        $tuples = [];
        $len = strlen($valuesSql);
        $i = 0;

        while ($i < $len) {
            if ($valuesSql[$i] !== '(') {
                $i++;
                continue;
            }

            [$tuple, $nextIndex] = $this->parseSingleTuple($valuesSql, $i);
            if (! empty($tuple)) {
                $tuples[] = $tuple;
            }
            $i = $nextIndex;
        }

        return $tuples;
    }

    /**
     * @return array{0: array<int, mixed>, 1: int} tuple data and next index
     */
    private function parseSingleTuple(string $s, int $startIndex): array
    {
        $tokens = [];
        $tokenBuffer = '';
        $tokenIsQuoted = false;
        $inString = false;

        $i = $startIndex + 1;
        $len = strlen($s);

        while ($i < $len) {
            $ch = $s[$i];

            if ($inString) {
                if ($ch === "'" && isset($s[$i + 1]) && $s[$i + 1] === "'") {
                    // SQL escape: '' inside strings -> a single '
                    $tokenBuffer .= "'";
                    $i += 2;
                    continue;
                }

                if ($ch === "'") {
                    $inString = false;
                    $i++;
                    continue;
                }

                $tokenBuffer .= $ch;
                $i++;
                continue;
            }

            if ($ch === "'") {
                $inString = true;
                $tokenIsQuoted = true;
                $tokenBuffer = '';
                $i++;
                continue;
            }

            if ($ch === ',') {
                $tokens[] = $this->convertSqlToken($tokenBuffer, $tokenIsQuoted);
                $tokenBuffer = '';
                $tokenIsQuoted = false;
                $i++;
                continue;
            }

            if ($ch === ')') {
                $tokens[] = $this->convertSqlToken($tokenBuffer, $tokenIsQuoted);
                return [$tokens, $i + 1];
            }

            $tokenBuffer .= $ch;
            $i++;
        }

        // If we fall out, tuple is malformed; return empty to ignore.
        return [[], $i];
    }

    private function convertSqlToken(string $tokenBuffer, bool $tokenIsQuoted): mixed
    {
        $token = trim($tokenBuffer);
        if ($tokenIsQuoted) {
            if ($token === '0000-00-00') {
                return null;
            }
            if ($token === '0000-00-00 00:00:00') {
                return null;
            }
            return $token;
        }

        if (strcasecmp($token, 'NULL') === 0 || $token === '') {
            return null;
        }

        if (preg_match('/^-?\\d+$/', $token)) {
            return (int) $token;
        }

        return $token;
    }
}

