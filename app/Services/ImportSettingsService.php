<?php

namespace App\Services;

use App\Models\BoutNumberScheme;
use App\Models\BoutNumberSchemeGroup;
use App\Models\Division;
use App\Models\DivisionPeriodSetting;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentMat;
use Illuminate\Support\Facades\DB;

class ImportSettingsService
{
    /**
     * Clear existing divisions, groups, and period settings for a tournament (used before copy).
     */
    public function clearDivisionsAndGroups(int $targetTid): void
    {
        $divisions = Division::where('Tournament_Id', $targetTid)->get();
        foreach ($divisions as $d) {
            DivisionPeriodSetting::where('division_id', $d->id)->delete();
            DivGroup::where('Division_id', $d->id)->where('Tournament_Id', $targetTid)->delete();
        }
        Division::where('Tournament_Id', $targetTid)->delete();
    }

    /**
     * Clear existing mats for a tournament.
     */
    public function clearMats(int $targetTid): void
    {
        TournamentMat::where('tournament_id', $targetTid)->delete();
    }

    /**
     * Clear existing number schemes (and their groups) for a tournament.
     */
    public function clearBoutNumbering(int $targetTid): void
    {
        $schemeIds = BoutNumberScheme::where('tournament_id', $targetTid)->pluck('id');
        BoutNumberSchemeGroup::whereIn('bout_number_scheme_id', $schemeIds)->delete();
        BoutNumberScheme::where('tournament_id', $targetTid)->delete();
    }

    /**
     * Copy divisions, their groups, and period timing from source tournament to target.
     * If clearFirst is true, existing divisions/groups in target are removed first.
     * Returns mapping: source_division_id => target_division_id.
     *
     * @return array<int, int>
     */
    public function copyDivisionsAndGroups(int $sourceTid, int $targetTid, bool $clearFirst = true): array
    {
        $source = Tournament::findOrFail($sourceTid);
        $target = Tournament::findOrFail($targetTid);
        $divisionMap = [];

        return DB::transaction(function () use ($source, $target, $clearFirst, &$divisionMap) {
            if ($clearFirst) {
                $this->clearDivisionsAndGroups($target->id);
            }
            $sourceDivisions = Division::where('Tournament_Id', $source->id)->orderBy('id')->get();

            foreach ($sourceDivisions as $srcDiv) {
                $newDiv = Division::create([
                    'DivisionName' => $srcDiv->DivisionName,
                    'StartingMat' => $srcDiv->StartingMat,
                    'TotalMats' => $srcDiv->TotalMats,
                    'PerBracket' => $srcDiv->PerBracket,
                    'Tournament_Id' => $target->id,
                    'bouted' => 0,
                    'Bracketed' => 0,
                    'printedbrackets' => false,
                    'printedbouts' => false,
                ]);
                $divisionMap[$srcDiv->id] = $newDiv->id;

                foreach ($srcDiv->divGroups as $srcGrp) {
                    DivGroup::create([
                        'id' => $srcGrp->id,
                        'Tournament_Id' => $target->id,
                        'Division_id' => $newDiv->id,
                        'Name' => $srcGrp->Name,
                        'gender' => $srcGrp->gender,
                        'MinAge' => $srcGrp->MinAge,
                        'MaxAge' => $srcGrp->MaxAge,
                        'MinGrade' => $srcGrp->MinGrade,
                        'MaxGrade' => $srcGrp->MaxGrade,
                        'MaxWeightDiff' => $srcGrp->MaxWeightDiff,
                        'BracketType' => $srcGrp->BracketType,
                        'MaxPwrDiff' => $srcGrp->MaxPwrDiff,
                        'MaxExpDiff' => $srcGrp->MaxExpDiff,
                        'bracketed' => 0,
                        'bouted' => 0,
                    ]);
                }

                foreach ($srcDiv->periodSettings as $ps) {
                    DivisionPeriodSetting::create([
                        'division_id' => $newDiv->id,
                        'period_code' => $ps->period_code,
                        'period_label' => $ps->period_label,
                        'sort_order' => $ps->sort_order,
                        'duration_seconds' => $ps->duration_seconds,
                    ]);
                }
            }

            return $divisionMap;
        });
    }

    /**
     * Copy all mats from source tournament to target. If clearFirst, existing mats are removed first.
     */
    public function copyMats(int $sourceTid, int $targetTid, bool $clearFirst = true): void
    {
        if ($clearFirst) {
            $this->clearMats($targetTid);
        }
        $mats = TournamentMat::where('tournament_id', $sourceTid)->get();
        foreach ($mats as $m) {
            TournamentMat::create([
                'tournament_id' => $targetTid,
                'mat_number' => $m->mat_number,
                'name' => $m->name,
                'constraint' => $m->constraint,
            ]);
        }
    }

    /**
     * Copy all number schemes (and their scheme groups) from source to target.
     * divisionMap: source_division_id => target_division_id (when copying with divisions). If empty, built by matching division names.
     * If clearFirst, existing schemes in target are removed first.
     *
     * @param  array<int, int>  $divisionMap
     */
    public function copyBoutNumbering(int $sourceTid, int $targetTid, array $divisionMap = [], bool $clearFirst = true): void
    {
        if ($clearFirst) {
            $this->clearBoutNumbering($targetTid);
        }
        if (empty($divisionMap)) {
            $divisionMap = $this->buildDivisionMapByName($sourceTid, $targetTid);
        }
        $schemes = BoutNumberScheme::where('tournament_id', $sourceTid)->get();

        foreach ($schemes as $src) {
            $newScheme = BoutNumberScheme::create([
                'tournament_id' => $targetTid,
                'scheme_name' => $src->scheme_name,
                'start_at' => $src->start_at,
                'skip_byes' => $src->skip_byes,
                'match_ids' => $src->match_ids,
                'all_mats' => $src->all_mats,
                'all_groups' => $src->all_groups,
                'all_rounds' => $src->all_rounds,
                'mat_numbers' => $src->mat_numbers,
                'round_numbers' => $src->round_numbers,
            ]);

            foreach ($src->schemeGroups as $sg) {
                $targetDivId = $divisionMap[$sg->division_id] ?? null;
                if ($targetDivId !== null) {
                    BoutNumberSchemeGroup::create([
                        'bout_number_scheme_id' => $newScheme->id,
                        'tournament_id' => $targetTid,
                        'division_id' => $targetDivId,
                        'group_id' => $sg->group_id,
                    ]);
                }
            }
        }
    }

    /**
     * Build source_division_id => target_division_id by matching division names (for bout numbering when divisions not copied).
     *
     * @return array<int, int>
     */
    private function buildDivisionMapByName(int $sourceTid, int $targetTid): array
    {
        $sourceDivs = Division::where('Tournament_Id', $sourceTid)->get()->keyBy('DivisionName');
        $targetDivs = Division::where('Tournament_Id', $targetTid)->get()->keyBy('DivisionName');
        $map = [];
        foreach ($sourceDivs as $name => $src) {
            if (isset($targetDivs[$name])) {
                $map[$src->id] = $targetDivs[$name]->id;
            }
        }
        return $map;
    }
}
