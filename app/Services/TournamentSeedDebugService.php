<?php

namespace App\Services;

use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\Wrestler;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a tournament with synthetic wrestlers for debugging.
 *
 * Assumes the tournament, divisions, and groups are already set up. Only creates:
 * - Wrestler records (wrestlers table, user_id = 1)
 * - TournamentWrestler records (adds them to the tournament)
 *
 * 80% boys, 20% girls per division. Assigns each wrestler to an existing group in that division.
 */
class TournamentSeedDebugService
{
    private const USER_ID = 1;
    private const CLUB = 'Seed Club';
    private const BOY_FIRST_NAMES = ['Alex', 'Blake', 'Cole', 'Drew', 'Evan', 'Finn', 'Gage', 'Hunter', 'Ivan', 'Jake', 'Kyle', 'Luke', 'Max', 'Nate', 'Owen', 'Paul', 'Quinn', 'Reed', 'Sam', 'Ty'];
    private const GIRL_FIRST_NAMES = ['Anna', 'Bella', 'Clara', 'Daisy', 'Emma', 'Faith', 'Grace', 'Hope', 'Ivy', 'Jade'];
    private const LAST_NAMES = ['Adams', 'Brown', 'Clark', 'Davis', 'Evans', 'Foster', 'Green', 'Hall', 'Irwin', 'Jones', 'King', 'Lee', 'Miller', 'Nelson', 'Olsen', 'Parker', 'Quinn', 'Reed', 'Smith', 'Taylor'];

    public function run(int $tournamentId, int $pwCount, int $jrCount): array
    {
        $tournament = Tournament::findOrFail($tournamentId);
        $divisions = Division::where('Tournament_Id', $tournamentId)->orderBy('id')->get();

        if ($divisions->isEmpty()) {
            throw new \InvalidArgumentException("Tournament {$tournamentId} has no divisions. Set up divisions and groups first.");
        }

        $pw = $divisions->firstWhere('DivisionName', 'PW') ?? $divisions->first();
        $jr = $divisions->count() >= 2 ? ($divisions->firstWhere('DivisionName', 'JR') ?? $divisions->get(1)) : null;

        $created = ['wrestlers' => 0, 'tournament_wrestlers' => 0];

        DB::transaction(function () use ($tournamentId, $pwCount, $jrCount, $pw, $jr, &$created) {
            if ($pw && $pwCount > 0) {
                $this->seedDivision($tournamentId, $pw, $pwCount, $created);
            }
            if ($jr && $jrCount > 0) {
                $this->seedDivision($tournamentId, $jr, $jrCount, $created);
            }
        });

        return $created;
    }

    private function seedDivision(int $tid, Division $division, int $count, array &$created): void
    {
        $allGroups = DivGroup::where('Tournament_Id', $tid)
            ->where('Division_id', $division->id)
            ->orderBy('id')
            ->get();

        if ($allGroups->isEmpty()) {
            throw new \InvalidArgumentException("Division \"{$division->DivisionName}\" (id {$division->id}) has no groups. Add groups first.");
        }

        $groups = $this->groupsForDivision($division, $allGroups);
        if ($groups->isEmpty()) {
            throw new \InvalidArgumentException("Division \"{$division->DivisionName}\": no matching groups (PW uses only P-1).");
        }

        $boysCount = (int) round($count * 0.8);
        $girlsCount = $count - $boysCount;
        $groupIndex = 0;

        for ($i = 0; $i < $boysCount; $i++) {
            $this->createAndRegisterWrestler($tid, $division, $groups, $groupIndex, 'Boy', $i + 1, $created);
        }
        for ($i = 0; $i < $girlsCount; $i++) {
            $this->createAndRegisterWrestler($tid, $division, $groups, $groupIndex, 'Girl', $i + 1, $created);
        }
    }

    /** PW: only P-1 group (Grades P - 1). JR/others: all groups. */
    private function groupsForDivision(Division $division, $allGroups)
    {
        if (stripos($division->DivisionName, 'PW') !== false) {
            return $allGroups->filter(function (DivGroup $g) {
                $name = (string) ($g->Name ?? '');
                return preg_match('/P\s*[-–]\s*1|Grades?\s*P\s*[-–]\s*1/i', $name) || stripos($name, 'P-1') !== false;
            })->values();
        }
        return $allGroups;
    }

    private function createAndRegisterWrestler(
        int $tid,
        Division $division,
        $groups,
        int &$groupIndex,
        string $gender,
        int $seq,
        array &$created
    ): void {
        $matchingGender = $this->groupsMatchingGender($groups, $gender);
        $group = $matchingGender->isNotEmpty()
            ? $matchingGender->get($groupIndex % $matchingGender->count())
            : $groups->get($groupIndex % $groups->count());
        $groupIndex++;

        $age = $this->ageForGroup($group);
        $grade = $this->gradeForGroup($group);
        $weight = $this->weightForWrestler($division, $group, $seq);

        $firstName = $gender === 'Girl'
            ? self::GIRL_FIRST_NAMES[$seq % count(self::GIRL_FIRST_NAMES)]
            : self::BOY_FIRST_NAMES[$seq % count(self::BOY_FIRST_NAMES)];
        $lastName = self::LAST_NAMES[$seq % count(self::LAST_NAMES)] . $seq;

        $wrestler = Wrestler::create([
            'wr_first_name' => $firstName,
            'wr_last_name' => $lastName,
            'wr_gender' => $gender,
            'wr_club' => self::CLUB,
            'wr_age' => $age,
            'wr_grade' => (string) $grade,
            'wr_weight' => $weight,
            'wr_pr' => $weight,
            'wr_wins' => 0,
            'wr_losses' => 0,
            'wr_years' => 1,
            'user_id' => self::USER_ID,
        ]);
        $created['wrestlers']++;

        TournamentWrestler::create([
            'wr_first_name' => $wrestler->wr_first_name,
            'wr_last_name' => $wrestler->wr_last_name,
            'wr_club' => $wrestler->wr_club,
            'wr_age' => $wrestler->wr_age,
            'wr_grade' => $wrestler->wr_grade,
            'wr_weight' => $wrestler->wr_weight,
            'wr_years' => $wrestler->wr_years,
            'wr_pr' => $wrestler->wr_pr,
            'group_id' => $group->id,
            'division_id' => $group->Division_id,
            'Wrestler_Id' => $wrestler->id,
            'Tournament_id' => $tid,
        ]);
        $created['tournament_wrestlers']++;
    }

    /** Groups that accept this gender (boys/girls/coed). Used to spread wrestlers across groups. */
    private function groupsMatchingGender($groups, string $gender): \Illuminate\Support\Collection
    {
        $genders = $gender === 'Girl' ? ['girls', 'coed'] : ['boys', 'coed'];
        return $groups->filter(function ($g) use ($genders) {
            return in_array((string) $g->gender, $genders, true);
        })->values();
    }

    /** Weight spread: PW P-1 roughly 40–90 lbs, JR roughly 70–170 lbs, with variance by seq. */
    private function weightForWrestler(Division $division, DivGroup $group, int $seq): int
    {
        $isPw = stripos($division->DivisionName, 'PW') !== false;
        if ($isPw) {
            $min = 40;
            $max = 90;
        } else {
            $min = 70;
            $max = 170;
        }
        $range = $max - $min + 1;
        $offset = ($seq * 7 + $seq * $seq) % $range;
        return $min + $offset;
    }

    private function ageForGroup(DivGroup $group): int
    {
        $min = (int) $group->MinAge;
        $max = (int) $group->MaxAge;
        return $min >= $max ? $min : (int) round(($min + $max) / 2);
    }

    private function gradeForGroup(DivGroup $group): int
    {
        $min = (int) $group->MinGrade;
        $max = (int) $group->MaxGrade;
        return $min >= $max ? $min : (int) round(($min + $max) / 2);
    }
}
