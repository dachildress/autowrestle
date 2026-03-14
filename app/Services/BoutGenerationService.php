<?php

namespace App\Services;

use App\Models\Bout;
use App\Models\BoutSetting;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use Illuminate\Support\Facades\DB;

class BoutGenerationService
{
    /**
     * Create bouts for all unbouted brackets in a division.
     * Distributes brackets across mats, uses boutsettings for round/position pairs.
     */
    public function createBoutsForDivision(int $tid, int $did): void
    {
        Division::where('id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);
        DivGroup::where('Division_id', $did)->where('Tournament_Id', $tid)->update(['bouted' => 1]);

        $unbouted = Bracket::where('Tournament_Id', $tid)
            ->where('Division_Id', $did)
            ->where('bouted', 0)
            ->distinct()
            ->pluck('id');

        $totalbrackets = $unbouted->count();
        if ($totalbrackets === 0) {
            return;
        }

        $division = Division::where('id', $did)->where('Tournament_Id', $tid)->firstOrFail();
        $startmat = (int) $division->StartingMat;
        $totalmats = (int) $division->TotalMats;
        $currentloop = $totalmats + 1;
        $currentmat = $startmat - 1;
        $notdone = true;

        while ($notdone) {
            $currentloop--;
            $currentmat++;
            $counter = 0;

            $maxBout = Bout::where('Tournament_Id', $tid)->where('mat_number', $currentmat)->max('id');
            if ($maxBout !== null && $maxBout >= 1) {
                $counter = (int) $maxBout - (1000 * $currentmat) + 1;
            }

            $permat = $this->bracketCount($totalbrackets, $currentloop, $totalmats);
            $boutnumber = 1;

            $bracketIds = Bracket::where('Tournament_Id', $tid)
                ->where('Division_Id', $did)
                ->where('bouted', 0)
                ->distinct()
                ->limit($permat)
                ->pluck('id');

            $num = $bracketIds->count();
            if ($num === 0) {
                $notdone = false;
                continue;
            }

            for ($round = 1; $round <= 5; $round++) {
                foreach ($bracketIds as $bracketId) {
                    Bracket::where('id', $bracketId)->where('Tournament_Id', $tid)->update(['bouted' => 1]);

                    $wrestlerCount = Bracket::where('id', $bracketId)->where('Tournament_Id', $tid)->count();
                    $settings = BoutSetting::where('BoutType', $wrestlerCount)
                        ->where('Round', $round)
                        ->orderBy('PosNumber')
                        ->get();

                    $loop = 0;
                    foreach ($settings as $s) {
                        $bracketRow = Bracket::where('id', $bracketId)
                            ->where('Tournament_Id', $tid)
                            ->where('wr_pos', $s->PosNumber)
                            ->first();

                        if ($bracketRow) {
                            $boutId = $boutnumber + (1000 * $currentmat) + $counter + (int) $s->AddTo;
                            Bout::insert([
                                'id' => $boutId,
                                'Wrestler_Id' => $bracketRow->wr_Id,
                                'Bracket_Id' => $bracketId,
                                'mat_number' => $currentmat,
                                'round' => $round,
                                'Tournament_Id' => $tid,
                                'Division_Id' => $did,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $loop++;
                        }
                    }
                    $counter += (int) ($loop / 2);
                }
            }
        }
    }

    private function bracketCount(int $numBrackets, int $matIndex, int $totalMats): int
    {
        return (int) floor($numBrackets / $totalMats) + (($numBrackets % $totalMats) >= $matIndex ? 1 : 0);
    }
}
