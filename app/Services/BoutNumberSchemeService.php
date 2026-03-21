<?php

namespace App\Services;

use App\Models\Bout;
use App\Models\BoutNumberScheme;
use App\Models\BoutSetting;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Runs BoutNumberScheme rows: creates bouts for in-scope brackets, assigns bout_number
 * (and stable id) and mat_number. Scoring continues to use bout id and mat_number.
 */
class BoutNumberSchemeService
{
    /**
     * All bout number schemes for the tournament that apply to the division, in stable order.
     *
     * @return Collection<int, BoutNumberScheme>
     */
    public function applicableSchemesForDivision(int $tid, int $did): Collection
    {
        return BoutNumberScheme::where('tournament_id', $tid)
            ->orderBy('scheme_name')
            ->orderBy('id')
            ->get()
            ->filter(fn (BoutNumberScheme $s) => $this->schemeAppliesToDivision($s, $tid, $did))
            ->values();
    }

    /**
     * Run every applicable scheme for this division: each scheme supplies its own mats, groups,
     * rounds, start_at, skip_byes, and same_mat_per_bracket. Bout ids continue globally across schemes.
     */
    public function runAllSchemesForDivision(int $tid, int $did): void
    {
        $schemes = $this->applicableSchemesForDivision($tid, $did);
        foreach ($schemes as $scheme) {
            $this->runSingleSchemeForDivision($tid, $did, $scheme, deferDivisionBoutedFlags: true);
        }

        Division::where('id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
    }

    /**
     * Create bouts for only the given scheme’s groups/mats/rounds (one pass).
     *
     * @param  bool  $deferDivisionBoutedFlags  When true, caller sets division/divgroup bouted after multiple schemes.
     */
    public function runSingleSchemeForDivision(int $tid, int $did, BoutNumberScheme $scheme, bool $deferDivisionBoutedFlags = false): void
    {
        $groupKeys = $this->resolveGroupKeysForDivision($scheme, $tid, $did);
        if ($groupKeys === []) {
            return;
        }

        $mats = $this->resolveMats($scheme, $tid);
        $mats = array_values(array_map('intval', $mats));
        if ($mats === []) {
            $mats = [1];
        }

        $rounds = $this->resolveRounds($scheme);
        if ($rounds === []) {
            $rounds = [1, 2, 3, 4, 5];
        }

        $nextId = (int) Bout::where('Tournament_Id', $tid)->max('id') + 1;
        if ($nextId < 1) {
            $nextId = 1;
        }
        $nextBoutNumber = (int) $scheme->start_at;
        $matIndex = 0;
        $matCount = count($mats);
        $sameMatPerBracket = (bool) $scheme->same_mat_per_bracket;
        $skipByes = (bool) $scheme->skip_byes;
        $bracketMatMap = [];

        $bracketRoundList = $this->orderedBracketRoundsForDivision($tid, $did, $groupKeys, $rounds);

        foreach ($bracketRoundList as $item) {
            $bracketId = $item['bracket_id'];
            $round = $item['round'];
            $divisionId = $item['division_id'];

            $wrestlerCount = Bracket::where('id', $bracketId)->where('Tournament_Id', $tid)->count();
            $settings = BoutSetting::where('BoutType', $wrestlerCount)
                ->where('Round', $round)
                ->orderBy('PosNumber')
                ->get();

            $settingsByAddTo = $settings->groupBy('AddTo');
            foreach ($settingsByAddTo as $addTo => $positionSettings) {
                $rows = [];
                foreach ($positionSettings as $s) {
                    $bracketRow = Bracket::where('id', $bracketId)
                        ->where('Tournament_Id', $tid)
                        ->where('wr_pos', $s->PosNumber)
                        ->first();
                    if ($bracketRow) {
                        $rows[] = $bracketRow;
                    }
                }
                if (empty($rows)) {
                    continue;
                }
                if ($skipByes && count($rows) < 2) {
                    continue;
                }

                $boutId = $nextId++;
                $boutNumber = $nextBoutNumber++;
                if ($sameMatPerBracket) {
                    if (! isset($bracketMatMap[$bracketId])) {
                        $bracketMatMap[$bracketId] = $mats[$matIndex % $matCount];
                        $matIndex++;
                    }
                    $matNumber = $bracketMatMap[$bracketId];
                } else {
                    $matNumber = $mats[$matIndex % $matCount];
                    $matIndex++;
                }

                foreach ($rows as $bracketRow) {
                    Bout::insert([
                        'id' => $boutId,
                        'bout_number' => $boutNumber,
                        'Wrestler_Id' => $bracketRow->wr_Id,
                        'Bracket_Id' => $bracketId,
                        'mat_number' => $matNumber,
                        'round' => $round,
                        'Tournament_Id' => $tid,
                        'Division_Id' => $divisionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $bracketIdsCreated = array_unique(array_column($bracketRoundList, 'bracket_id'));
        foreach ($bracketIdsCreated as $bid) {
            Bracket::where('id', $bid)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
        }

        if (! $deferDivisionBoutedFlags) {
            Division::where('id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
            DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
        }
    }

    /**
     * Run a single scheme by id (sets division/divgroup bouted when done).
     */
    public function runSchemeForDivision(int $tid, int $did, int $schemeId): void
    {
        $scheme = BoutNumberScheme::where('id', $schemeId)
            ->where('tournament_id', $tid)
            ->firstOrFail();

        $this->runSingleSchemeForDivision($tid, $did, $scheme, deferDivisionBoutedFlags: false);
    }

    /**
     * Mat pool for rotating bouts under this scheme (order is rotation order).
     *
     * - all_mats: mats from Mat setup ({@see TournamentMat}), ordered by mat_number — not a random pluck order.
     *   If no rows in tournament_mats, falls back to {@see Tournament::getConfiguredMatNumbers()} (divisions),
     *   then to existing bouts / 1..max.
     * - explicit mat_numbers: exactly those mats, in the order stored on the scheme (operator-selected order).
     */
    public function resolveMats(BoutNumberScheme $scheme, int $tid): array
    {
        $tournament = Tournament::find($tid);

        if ($scheme->all_mats) {
            if ($tournament !== null) {
                $fromSetup = $tournament->tournamentMats()
                    ->orderBy('mat_number')
                    ->pluck('mat_number')
                    ->map(fn ($m) => (int) $m)
                    ->unique()
                    ->values()
                    ->all();
                if ($fromSetup !== []) {
                    return $fromSetup;
                }
            }

            $configured = $tournament ? $tournament->getConfiguredMatNumbers() : [];
            if ($configured !== []) {
                $configured = array_map('intval', $configured);
                $configured = array_values(array_unique($configured));
                sort($configured);

                return $configured;
            }

            $maxMat = Bout::where('Tournament_Id', $tid)->max('mat_number');
            if ($maxMat === null) {
                $maxMat = 3;
            }

            return range(1, (int) $maxMat);
        }

        $nums = $scheme->mat_numbers;

        return is_array($nums) && $nums !== [] ? array_values(array_map('intval', $nums)) : [];
    }

    /**
     * Resolve round numbers for the scheme (explicit list or 1..5).
     */
    public function resolveRounds(BoutNumberScheme $scheme): array
    {
        if ($scheme->all_rounds) {
            return [1, 2, 3, 4, 5];
        }
        $nums = $scheme->round_numbers;
        return is_array($nums) ? array_values(array_map('intval', $nums)) : [1, 2, 3, 4, 5];
    }

    /**
     * Resolve (division_id, group_id) pairs that this scheme applies to for the given division.
     * Returns array of ['division_id' => x, 'group_id' => y].
     */
    public function resolveGroupKeysForDivision(BoutNumberScheme $scheme, int $tid, int $did): array
    {
        if ($scheme->all_groups) {
            return DivGroup::where('Tournament_Id', $tid)
                ->where('Division_id', $did)
                ->get()
                ->map(fn ($g) => ['division_id' => (int) $g->Division_id, 'group_id' => (int) $g->id])
                ->values()
                ->all();
        }
        return $scheme->schemeGroups()
            ->where('tournament_id', $tid)
            ->where('division_id', $did)
            ->get()
            ->map(fn ($sg) => ['division_id' => (int) $sg->division_id, 'group_id' => (int) $sg->group_id])
            ->values()
            ->all();
    }

    /**
     * Ordered list of (bracket_id, round, division_id) for numbering: round first, then group, then bracket.
     * So bout numbers run sequentially by round: all round 1 (4000..4036), then round 2 (4037..), etc.
     */
    private function orderedBracketRoundsForDivision(int $tid, int $did, array $groupKeys, array $rounds): array
    {
        $groups = DivGroup::where('Tournament_Id', $tid)
            ->where('Division_id', $did)
            ->whereIn('id', array_column($groupKeys, 'group_id'))
            ->orderBy('MinGrade')
            ->orderBy('MaxGrade')
            ->orderBy('id')
            ->get();

        $result = [];
        foreach ($rounds as $round) {
            foreach ($groups as $group) {
                $bracketIds = Bracket::where('Tournament_Id', $tid)
                    ->where('Division_Id', $did)
                    ->whereIn('id', TournamentWrestler::where('Tournament_id', $tid)
                        ->where('division_id', $did)
                        ->where('group_id', $group->id)
                        ->whereNotNull('wr_bracket_id')
                        ->pluck('wr_bracket_id'))
                    ->where('bouted', 0)
                    ->distinct()
                    ->orderBy('id')
                    ->pluck('id');

                foreach ($bracketIds as $bracketId) {
                    $result[] = [
                        'bracket_id' => $bracketId,
                        'round' => $round,
                        'division_id' => $did,
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Whether the given scheme applies to the given division (has at least one group in scope).
     */
    public function schemeAppliesToDivision(BoutNumberScheme $scheme, int $tid, int $did): bool
    {
        return count($this->resolveGroupKeysForDivision($scheme, $tid, $did)) > 0;
    }

    /**
     * Prefer a scheme that has explicit mat_numbers for this division (e.g. JR on mats 5–6)
     * over a scheme that uses all_mats (which would assign 1–6). Returns null if none apply.
     */
    public function getPreferredSchemeForDivision(int $tid, int $did): ?BoutNumberScheme
    {
        $applicable = BoutNumberScheme::where('tournament_id', $tid)
            ->get()
            ->filter(fn (BoutNumberScheme $s) => $this->schemeAppliesToDivision($s, $tid, $did));

        if ($applicable->isEmpty()) {
            return null;
        }

        // Prefer schemes with explicit mat_numbers (all_mats = false) so division-specific mats (e.g. JR → 5,6) win
        $withExplicitMats = $applicable->filter(fn (BoutNumberScheme $s) => ! $s->all_mats && is_array($s->mat_numbers) && ! empty($s->mat_numbers));
        if ($withExplicitMats->isNotEmpty()) {
            return $withExplicitMats->first();
        }

        return $applicable->first();
    }

    /**
     * Whether the tournament has at least one scheme that applies to the given division.
     */
    public function divisionHasScheme(int $tid, int $did): bool
    {
        $schemes = BoutNumberScheme::where('tournament_id', $tid)->get();
        foreach ($schemes as $scheme) {
            if ($this->schemeAppliesToDivision($scheme, $tid, $did)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mat numbers relevant to this division for display / reference on bout sheets.
     *
     * Important: {@see resolveMats} with all_mats=true uses only {@see Tournament::getConfiguredMatNumbers},
     * which returns tournament_mats rows if any exist — often just mat 1. That must NOT be the only
     * source of truth for printing. We always merge:
     * - mats from every applicable bout scheme,
     * - this division's StartingMat … StartingMat + TotalMats − 1,
     * - every distinct mat_number already stored on bouts for this division (and via wrestlers in this division).
     *
     * Bout printing does not filter by this list (so mats are never dropped); this is for UI hints / reports.
     *
     * @return list<int>
     */
    public function resolvePrintMatsForDivision(int $tid, int $did): array
    {
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->first();
        if ($division === null) {
            return [];
        }

        $matUnion = [];

        $applicable = BoutNumberScheme::where('tournament_id', $tid)
            ->get()
            ->filter(fn (BoutNumberScheme $s) => $this->schemeAppliesToDivision($s, $tid, $did));

        foreach ($applicable as $scheme) {
            $matUnion = array_merge($matUnion, $this->resolveMats($scheme, $tid));
        }

        $start = (int) $division->StartingMat;
        $total = max(1, (int) $division->TotalMats);
        $matUnion = array_merge($matUnion, range($start, $start + $total - 1));

        $fromBoutColumn = DB::table('bouts')
            ->where('Tournament_Id', $tid)
            ->where('Division_Id', $did)
            ->pluck('mat_number');

        $fromWrestlers = DB::table('bouts as b')
            ->join('tournamentwrestlers as tw', function ($join) use ($tid) {
                $join->on('tw.id', '=', 'b.Wrestler_Id')
                    ->where('tw.Tournament_id', '=', $tid);
            })
            ->where('b.Tournament_Id', $tid)
            ->where('tw.division_id', $did)
            ->pluck('b.mat_number');

        foreach ($fromBoutColumn->merge($fromWrestlers) as $m) {
            if ($m !== null && $m !== '') {
                $matUnion[] = (int) $m;
            }
        }

        $matUnion = array_values(array_unique(array_filter(
            array_map('intval', $matUnion),
            static fn (int $m) => $m >= 1
        )));
        sort($matUnion);

        return $matUnion;
    }

    /**
     * Union of group ids from every bout scheme that applies to this division.
     * Null means no scheme applies — do not filter bouts by group (legacy tournaments).
     *
     * @return list<int>|null
     */
    public function resolvePrintGroupIdsForDivision(int $tid, int $did): ?array
    {
        $applicable = BoutNumberScheme::where('tournament_id', $tid)
            ->get()
            ->filter(fn (BoutNumberScheme $s) => $this->schemeAppliesToDivision($s, $tid, $did));

        if ($applicable->isEmpty()) {
            return null;
        }

        $groupUnion = [];
        foreach ($applicable as $scheme) {
            foreach ($this->resolveGroupKeysForDivision($scheme, $tid, $did) as $row) {
                $groupUnion[] = (int) $row['group_id'];
            }
        }

        return array_values(array_unique($groupUnion));
    }
}
