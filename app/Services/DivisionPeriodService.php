<?php

namespace App\Services;

use App\Models\DivisionPeriodSetting;

/**
 * Division period timing: load config, next period, match complete, defaults.
 * Period numbering: 1=Period 1, 2=Period 2, 3=Period 3, 4=OT1, 5=OT2, 6=OT3.
 */
class DivisionPeriodService
{
    public const PERIOD_CODES = ['1', '2', '3', 'OT1', 'OT2', 'OT3'];

    /** Default duration_seconds by period_code (fallback when division has no settings). */
    public const DEFAULT_DURATIONS = [
        '1' => 90,   // 1:30
        '2' => 60,   // 1:00
        '3' => 60,   // 1:00
        'OT1' => 60,
        'OT2' => 30,
        'OT3' => 30,
    ];

    /**
     * Get all period settings for a division, ordered by sort_order.
     * @return \Illuminate\Support\Collection<int, DivisionPeriodSetting>
     */
    public function getSettingsForDivision(int $divisionId): \Illuminate\Support\Collection
    {
        return DivisionPeriodSetting::where('division_id', $divisionId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get duration in seconds for a period. Uses division config or fallback defaults.
     */
    public function getPeriodDuration(int $divisionId, string $periodCode): int
    {
        $setting = DivisionPeriodSetting::where('division_id', $divisionId)
            ->where('period_code', $periodCode)
            ->first();
        if ($setting) {
            return (int) $setting->duration_seconds;
        }
        return self::DEFAULT_DURATIONS[$periodCode] ?? 60;
    }

    /**
     * Get duration for period number (1-6). Maps: 1->'1', 2->'2', 3->'3', 4->'OT1', 5->'OT2', 6->'OT3'.
     */
    public function getPeriodDurationByNumber(int $divisionId, int $periodNumber): int
    {
        $code = $this->periodNumberToCode($periodNumber);
        return $code ? $this->getPeriodDuration($divisionId, $code) : 60;
    }

    /**
     * Ordered list of period codes for display/sequence.
     * @return array<int, string>
     */
    public function getOrderedPeriodCodes(): array
    {
        return self::PERIOD_CODES;
    }

    /**
     * Get ordered periods for a division (with durations). For admin display.
     * @return array<int, array{period_code: string, period_label: string, sort_order: int, duration_seconds: int}>
     */
    public function getOrderedPeriods(int $divisionId): array
    {
        $settings = $this->getSettingsForDivision($divisionId);
        $result = [];
        $sortOrder = 1;
        foreach (self::PERIOD_CODES as $code) {
            $row = $settings->firstWhere('period_code', $code);
            $result[] = [
                'period_code' => $code,
                'period_label' => $row ? $row->period_label : $this->defaultLabel($code),
                'sort_order' => $sortOrder,
                'duration_seconds' => $row ? (int) $row->duration_seconds : self::DEFAULT_DURATIONS[$code],
            ];
            $sortOrder++;
        }
        return $result;
    }

    /**
     * Next period number (1-6) or null if match ends. Respects tie/overtime rules.
     */
    public function getNextPeriod(int $currentPeriodNumber, int $redScore, int $greenScore): ?int
    {
        $tied = ($redScore === $greenScore);

        switch ($currentPeriodNumber) {
            case 1:
                return 2;
            case 2:
                return 3;
            case 3:
                return $tied ? 4 : null;
            case 4:
                return $tied ? 5 : null;
            case 5:
                return 6;
            case 6:
                return null;
            default:
                return null;
        }
    }

    /**
     * True if the match is complete (no more periods) based on current period and scores.
     */
    public function isMatchComplete(int $currentPeriodNumber, int $redScore, int $greenScore): bool
    {
        return $this->getNextPeriod($currentPeriodNumber, $redScore, $greenScore) === null;
    }

    /**
     * True if match ended at OT3 still tied (needs tiebreaker/manual resolution).
     */
    public function isTiedAfterOT3(int $currentPeriodNumber, int $redScore, int $greenScore): bool
    {
        return $currentPeriodNumber === 6 && $redScore === $greenScore;
    }

    /**
     * Create default period rows for a division. Idempotent: only inserts missing period_codes.
     */
    public function initializeDefaultsForDivision(int $divisionId): int
    {
        $existing = DivisionPeriodSetting::where('division_id', $divisionId)
            ->pluck('period_code')
            ->all();
        $sortOrder = 1;
        $created = 0;
        foreach (self::PERIOD_CODES as $code) {
            if (in_array($code, $existing, true)) {
                $sortOrder++;
                continue;
            }
            DivisionPeriodSetting::create([
                'division_id' => $divisionId,
                'period_code' => $code,
                'period_label' => $this->defaultLabel($code),
                'sort_order' => $sortOrder,
                'duration_seconds' => self::DEFAULT_DURATIONS[$code],
            ]);
            $created++;
            $sortOrder++;
        }
        return $created;
    }

    /**
     * Map period number (1-6) to period_code.
     */
    public function periodNumberToCode(int $periodNumber): ?string
    {
        $map = [1 => '1', 2 => '2', 3 => '3', 4 => 'OT1', 5 => 'OT2', 6 => 'OT3'];
        return $map[$periodNumber] ?? null;
    }

    /**
     * Map period_code to period number (1-6).
     */
    public function periodCodeToNumber(string $periodCode): ?int
    {
        $map = ['1' => 1, '2' => 2, '3' => 3, 'OT1' => 4, 'OT2' => 5, 'OT3' => 6];
        return $map[$periodCode] ?? null;
    }

    private function defaultLabel(string $code): string
    {
        return match ($code) {
            '1' => 'Period 1',
            '2' => 'Period 2',
            '3' => 'Period 3',
            'OT1' => 'OT1',
            'OT2' => 'OT2',
            'OT3' => 'OT3',
            default => $code,
        };
    }
}
