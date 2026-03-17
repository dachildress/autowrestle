<?php

namespace App\Services;

use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\TournamentWrestler;

class BracketGenerationService
{
    /**
     * Create brackets for all unbracketed groups in a division.
     * Sets division and each group as bracketed only if at least one bracket was created.
     * Returns the number of bracket rows created.
     */
    public function createBracketsForDivision(int $tid, int $did): int
    {
        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $groups = DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->get();
        $totalBracketRows = 0;

        foreach ($groups as $group) {
            $totalBracketRows += $this->bracketGroup(
                (int) $group->id,
                $group->BracketType ?? '6',
                (int) $group->MaxWeightDiff,
                (int) $group->MaxExpDiff,
                $tid,
                $did,
                (int) $division->PerBracket
            );
        }

        if ($totalBracketRows > 0) {
            Division::where('id', $did)->where('Tournament_Id', $tid)->update(['Bracketed' => 1]);
        }

        return $totalBracketRows;
    }

    /**
     * Fill one group: repeatedly take the next unbracketed wrestler (by wr_pr, wr_years),
     * find others within weight/experience range, form a bracket, insert rows, mark wrestlers bracketed.
     * Returns the number of bracket rows inserted for this group.
     */
    public function bracketGroup(
        int $groupid,
        string $bracketType,
        int $weightDiff,
        int $expDiff,
        int $tid,
        int $div,
        int $divisionPerBracket
    ): int {
        $perBracket = $this->resolvePerBracket($bracketType, $divisionPerBracket);
        $perBracket = max(2, min(6, $perBracket));

        $rowsCreated = 0;
        $complete = false;
        while (! $complete) {
            $first = TournamentWrestler::where('Tournament_id', $tid)
                ->where('group_id', $groupid)
                ->where('bracketed', 0)
                ->orderByRaw('COALESCE(wr_pr, wr_weight, 0) ASC')
                ->orderByRaw('COALESCE(wr_years, 0) ASC')
                ->first();

            if (! $first) {
                $complete = true;
                continue;
            }

            $weight = (float) ($first->wr_pr ?? $first->wr_weight ?? 0);
            $years = (int) ($first->wr_years ?? 0);
            $lowEnd = $weight - $weightDiff;
            $highEnd = $weight + $weightDiff;
            $lowYears = $years - $expDiff;
            $highYears = $years + $expDiff;
            $limit = $perBracket - 1;

            $others = TournamentWrestler::where('Tournament_id', $tid)
                ->where('group_id', $groupid)
                ->where('bracketed', 0)
                ->where('id', '!=', $first->id)
                ->whereRaw('COALESCE(wr_pr, wr_weight, 0) >= ?', [$lowEnd])
                ->whereRaw('COALESCE(wr_pr, wr_weight, 0) <= ?', [$highEnd])
                ->whereRaw('COALESCE(wr_years, 0) >= ?', [$lowYears])
                ->whereRaw('COALESCE(wr_years, 0) <= ?', [$highYears])
                ->orderByRaw('COALESCE(wr_pr, wr_weight, 0) ASC, COALESCE(wr_years, 0) ASC')
                ->limit($limit)
                ->get();

            $nextBracket = (int) Bracket::where('Tournament_Id', $tid)->max('id') + 1;
            if ($nextBracket < 1) {
                $nextBracket = 1;
            }

            $pos = 0;
            $this->addWrestlerToBracket($first->id, $nextBracket, $pos++, $tid, $div);
            $this->markBracketed($first->id, $nextBracket, $pos - 1);
            $rowsCreated++;

            foreach ($others as $w) {
                $this->addWrestlerToBracket($w->id, $nextBracket, $pos++, $tid, $div);
                $this->markBracketed($w->id, $nextBracket, $pos - 1);
                $rowsCreated++;
            }
        }

        if ($rowsCreated > 0) {
            DivGroup::where('id', $groupid)
                ->where('Tournament_Id', $tid)
                ->where('Division_id', $div)
                ->update(['bracketed' => 1]);
        }

        return $rowsCreated;
    }

    private function resolvePerBracket(string $bracketType, int $divisionPerBracket): int
    {
        if (is_numeric(trim($bracketType))) {
            return (int) $bracketType;
        }
        return $divisionPerBracket ?: 6;
    }

    private function addWrestlerToBracket(int $wrId, int $bracketId, int $pos, int $tid, int $div): void
    {
        Bracket::insert([
            'id' => $bracketId,
            'wr_Id' => $wrId,
            'wr_pos' => $pos,
            'bouted' => 0,
            'Tournament_Id' => $tid,
            'Division_Id' => $div,
            'printed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function markBracketed(int $twId, int $bracketId, int $pos): void
    {
        TournamentWrestler::where('id', $twId)->update([
            'bracketed' => 1,
            'wr_bracket_id' => $bracketId,
            'wr_bracket_position' => $pos,
        ]);
    }
}
