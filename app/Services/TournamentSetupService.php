<?php

namespace App\Services;

use App\Models\Division;
use App\Models\DivisionPeriodSetting;
use App\Models\DivGroup;
use App\Models\Tournament;

/**
 * Creates default divisions, groups, and period settings for a new tournament.
 * Called when a tournament is first created so it has a usable structure.
 */
class TournamentSetupService
{
    public function __construct(
        private DivisionPeriodService $periodService
    ) {}

    /**
     * Create default divisions (PW, JR), their groups, and period times for the tournament.
     * Matches the structure from the division/group management screens.
     */
    public function createDefaultStructure(Tournament $tournament): void
    {
        $tid = $tournament->id;

        // Division PW: Start Mat 1, 4 mats, 4 wrestlers per bracket
        $pw = Division::create([
            'DivisionName' => 'PW',
            'StartingMat' => 1,
            'TotalMats' => 4,
            'PerBracket' => 4,
            'Tournament_Id' => $tid,
            'bouted' => 0,
            'Bracketed' => 0,
            'printedbrackets' => 0,
            'printedbouts' => 0,
        ]);
        $this->createPwGroups($tid, $pw->id);
        $this->createDefaultPeriodSettings($pw->id);

        // Division JR: Start Mat 5, 3 mats, 5 wrestlers per bracket
        $jr = Division::create([
            'DivisionName' => 'JR',
            'StartingMat' => 5,
            'TotalMats' => 3,
            'PerBracket' => 5,
            'Tournament_Id' => $tid,
            'bouted' => 0,
            'Bracketed' => 0,
            'printedbrackets' => 0,
            'printedbouts' => 0,
        ]);
        $this->createJrGroups($tid, $jr->id);
        $this->createDefaultPeriodSettings($jr->id);
    }

    /** PW division groups: Grades P-1, 2-3, 4-5 */
    private function createPwGroups(int $tid, int $divisionId): void
    {
        $groups = [
            ['Name' => 'Grades P - 1', 'gender' => 'coed', 'MinAge' => 3, 'MaxAge' => 7, 'MinGrade' => -1, 'MaxGrade' => 1, 'MaxWeightDiff' => 7, 'MaxPwrDiff' => 0, 'MaxExpDiff' => 1],
            ['Name' => 'Grades 2 - 3', 'gender' => 'coed', 'MinAge' => 7, 'MaxAge' => 9, 'MinGrade' => 2, 'MaxGrade' => 3, 'MaxWeightDiff' => 7, 'MaxPwrDiff' => 0, 'MaxExpDiff' => 2],
            ['Name' => 'Grades 4 - 5', 'gender' => 'coed', 'MinAge' => 9, 'MaxAge' => 11, 'MinGrade' => 4, 'MaxGrade' => 5, 'MaxWeightDiff' => 8, 'MaxPwrDiff' => 0, 'MaxExpDiff' => 3],
        ];
        $this->createGroupsForDivision($tid, $divisionId, $groups, '4');
    }

    /** JR division groups (Middle): Grades 6-8 */
    private function createJrGroups(int $tid, int $divisionId): void
    {
        $groups = [
            ['Name' => 'Grades 6 - 8', 'gender' => 'coed', 'MinAge' => 11, 'MaxAge' => 15, 'MinGrade' => 6, 'MaxGrade' => 8, 'MaxWeightDiff' => 8, 'MaxPwrDiff' => 2, 'MaxExpDiff' => 3],
        ];
        $this->createGroupsForDivision($tid, $divisionId, $groups, '5');
    }

    private function createGroupsForDivision(int $tid, int $divisionId, array $groups, string $bracketType): void
    {
        $gid = 1;
        foreach ($groups as $g) {
            DivGroup::create([
                'id' => $gid,
                'Name' => $g['Name'],
                'gender' => $g['gender'] ?? 'boys',
                'MinAge' => $g['MinAge'],
                'MaxAge' => $g['MaxAge'],
                'MinGrade' => $g['MinGrade'],
                'MaxGrade' => $g['MaxGrade'],
                'MaxWeightDiff' => $g['MaxWeightDiff'],
                'BracketType' => $bracketType,
                'MaxPwrDiff' => $g['MaxPwrDiff'],
                'bracketed' => 0,
                'bouted' => 0,
                'MaxExpDiff' => $g['MaxExpDiff'],
                'Tournament_Id' => $tid,
                'Division_id' => $divisionId,
            ]);
            $gid++;
        }
    }

    private function createDefaultPeriodSettings(int $divisionId): void
    {
        $codes = DivisionPeriodService::PERIOD_CODES;
        $durations = DivisionPeriodService::DEFAULT_DURATIONS;
        $sortOrder = 1;
        foreach ($codes as $code) {
            $label = in_array($code, ['1', '2', '3'], true) ? 'Period ' . $code : $code;
            DivisionPeriodSetting::create([
                'division_id' => $divisionId,
                'period_code' => $code,
                'period_label' => $label,
                'sort_order' => $sortOrder++,
                'duration_seconds' => $durations[$code] ?? 60,
            ]);
        }
    }
}
