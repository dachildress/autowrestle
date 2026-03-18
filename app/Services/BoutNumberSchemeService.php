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
use Illuminate\Support\Facades\DB;

/**
 * Runs a BoutNumberScheme: creates bouts for in-scope brackets, assigns bout_number
 * (and stable id) and mat_number. Scoring continues to use bout id and mat_number.
 */
class BoutNumberSchemeService
{
    /**
     * Create bouts for a division using a specific scheme.
     * Scheme must apply to at least one group in the division.
     *
     * @param  int  $tid  Tournament id
     * @param  int  $did  Division id
     * @param  int  $schemeId  BoutNumberScheme id (must belong to tournament and apply to this division)
     */
    public function runSchemeForDivision(int $tid, int $did, int $schemeId): void
    {
        $scheme = BoutNumberScheme::where('id', $schemeId)
            ->where('tournament_id', $tid)
            ->firstOrFail();

        $mats = $this->resolveMats($scheme, $tid);
        $rounds = $this->resolveRounds($scheme);
        $groupKeys = $this->resolveGroupKeysForDivision($scheme, $tid, $did);

        if (empty($groupKeys)) {
            return;
        }

        $nextId = (int) Bout::where('Tournament_Id', $tid)->max('id') + 1;
        if ($nextId < 1) {
            $nextId = 1;
        }
        $nextBoutNumber = (int) $scheme->start_at;
        $matIndex = 0;
        $matCount = count($mats);
        if ($matCount === 0) {
            $mats = [1];
            $matCount = 1;
        }

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
                if ($scheme->skip_byes && count($rows) < 2) {
                    continue;
                }

                $boutId = $nextId++;
                $boutNumber = $nextBoutNumber++;
                $matNumber = $mats[$matIndex % $matCount];
                $matIndex++;

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
        Division::where('id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
    }

    /**
     * Resolve mat numbers for the scheme (explicit list or all tournament mats).
     * Uses operator-defined tournament_mats when present; otherwise fallback to existing bouts or 1..3.
     */
    public function resolveMats(BoutNumberScheme $scheme, int $tid): array
    {
        $tournament = Tournament::find($tid);
        $configured = $tournament ? $tournament->getConfiguredMatNumbers() : [];

        if ($scheme->all_mats) {
            if (! empty($configured)) {
                return $configured;
            }
            $maxMat = Bout::where('Tournament_Id', $tid)->max('mat_number');
            if ($maxMat === null) {
                $maxMat = 3;
            }
            return range(1, (int) $maxMat);
        }
        $nums = $scheme->mat_numbers;
        return is_array($nums) ? array_values(array_map('intval', $nums)) : [];
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
     * Ordered list of (bracket_id, round, division_id) for numbering: lowest grade first, then bracket, then round.
     * Ensures all rounds for one bracket are adjacent so round-1 bouts are back-to-back.
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
                foreach ($rounds as $round) {
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
}
