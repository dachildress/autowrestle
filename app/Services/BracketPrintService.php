<?php

namespace App\Services;

use App\Models\Bout;
use App\Models\BoutSetting;
use App\Models\Bracket;
use App\Models\DivGroup;
use App\Models\TournamentWrestler;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Builds round-robin bracket sheets for printing (pairings from {@see BoutSetting}, same as {@see BoutGenerationService}).
 */
class BracketPrintService
{
    public const MAX_BOUT_TYPE = 6;

    /**
     * @return list<array{
     *   bracket_id: int,
     *   bracket_ordinal: int,
     *   group: DivGroup|null,
     *   weight_class: string,
     *   wrestlers: list<array{letter: string, pos: int, id: int, name: string, school: string}>,
     *   rounds: list<array{num: int, label: string, matches: list<array{a: array, b: array, bout_display: string|null}>}>,
     *   schedule_available: bool,
     * }>
     */
    public function buildDivisionSheets(int $tournamentId, int $divisionId, bool $divisionBouted): array
    {
        $bracketIds = Bracket::query()
            ->where('Tournament_Id', $tournamentId)
            ->where('Division_Id', $divisionId)
            ->distinct()
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $boutLookup = $this->buildBoutLookup($tournamentId, $divisionId, $divisionBouted);

        $sheets = [];
        $ordinal = 0;
        foreach ($bracketIds as $bracketId) {
            $ordinal++;
            $slots = Bracket::query()
                ->where('id', $bracketId)
                ->where('Tournament_Id', $tournamentId)
                ->orderBy('wr_pos')
                ->get();

            $twIds = $slots->pluck('wr_Id')->all();
            $tws = TournamentWrestler::query()
                ->whereIn('id', $twIds)
                ->get()
                ->keyBy('id');

            $posToTw = [];
            foreach ($slots as $slot) {
                $posToTw[(int) $slot->wr_pos] = (int) $slot->wr_Id;
            }

            $wrestlers = [];
            foreach ($slots as $slot) {
                $tw = $tws->get($slot->wr_Id);
                $pos = (int) $slot->wr_pos;
                $wrestlers[] = [
                    'letter' => chr(65 + $pos),
                    'pos' => $pos,
                    'id' => (int) $slot->wr_Id,
                    'name' => $tw ? trim($tw->wr_first_name . ' ' . $tw->wr_last_name) : '—',
                    'school' => $tw ? (string) ($tw->wr_club ?? '') : '',
                ];
            }

            $count = count($wrestlers);
            $firstTw = $tws->get($slots->first()?->wr_Id);
            $group = $firstTw
                ? DivGroup::query()
                    ->where('id', $firstTw->group_id)
                    ->where('Tournament_Id', $tournamentId)
                    ->where('Division_id', $divisionId)
                    ->first()
                : null;

            $weightClass = $this->weightClassLabel($wrestlers, $tws);

            $rounds = [];
            $scheduleAvailable = $count >= 2 && $count <= self::MAX_BOUT_TYPE;
            if ($scheduleAvailable) {
                $rounds = $this->buildRoundsForBoutType($count, $posToTw, (int) $bracketId, $boutLookup, $tws);
            }

            $sheets[] = [
                'bracket_id' => (int) $bracketId,
                'bracket_ordinal' => $ordinal,
                'group' => $group,
                'weight_class' => $weightClass,
                'wrestlers' => $wrestlers,
                'rounds' => $rounds,
                'schedule_available' => $scheduleAvailable,
            ];
        }

        return $sheets;
    }

    /**
     * @param  array<int, int>  $posToTw  wr_pos => tournament_wrestler id
     * @return list<array{num: int, label: string, matches: list<array{a: array, b: array, bout_display: string|null}>}>
     */
    private function buildRoundsForBoutType(int $boutType, array $posToTw, int $bracketId, array $boutLookup, BaseCollection $tws): array
    {
        $settings = BoutSetting::query()
            ->where('BoutType', $boutType)
            ->orderBy('Round')
            ->orderBy('AddTo')
            ->orderBy('PosNumber')
            ->get();

        $byRound = $settings->groupBy('Round')->sortKeys();
        $out = [];

        foreach ($byRound as $roundNum => $rows) {
            /** @var BaseCollection<int, BoutSetting> $rows */
            $groups = $rows->groupBy('AddTo');
            $matches = [];
            foreach ($groups as $groupRows) {
                $positions = $groupRows->sortBy('PosNumber')->pluck('PosNumber')->unique()->values()->all();
                if (count($positions) < 2) {
                    continue;
                }
                $p1 = (int) $positions[0];
                $p2 = (int) $positions[1];
                $id1 = $posToTw[$p1] ?? null;
                $id2 = $posToTw[$p2] ?? null;
                if ($id1 === null || $id2 === null) {
                    continue;
                }
                $a = $this->participantFromPos($p1, $id1, $tws);
                $b = $this->participantFromPos($p2, $id2, $tws);
                $boutDisplay = $this->lookupBoutDisplay($boutLookup, $bracketId, (int) $roundNum, $id1, $id2);
                $matches[] = [
                    'a' => $a,
                    'b' => $b,
                    'bout_display' => $boutDisplay,
                ];
            }

            usort($matches, function ($x, $y) {
                return $x['a']['pos'] <=> $y['a']['pos'];
            });

            if ($matches !== []) {
                $out[] = [
                    'num' => (int) $roundNum,
                    'label' => 'Round ' . $roundNum,
                    'matches' => $matches,
                ];
            }
        }

        return $out;
    }

    private function participantFromPos(int $pos, int $twId, BaseCollection $tws): array
    {
        $tw = $tws->get($twId);

        return [
            'pos' => $pos,
            'letter' => chr(65 + $pos),
            'tw_id' => $twId,
            'name' => $tw ? trim($tw->wr_first_name . ' ' . $tw->wr_last_name) : '—',
        ];
    }

    /** @return array<string, string> keyed for bracket+round+wrestler pair */
    private function buildBoutLookup(int $tournamentId, int $divisionId, bool $divisionBouted): array
    {
        if (! $divisionBouted) {
            return [];
        }

        $bouts = Bout::query()
            ->where('Tournament_Id', $tournamentId)
            ->where('Division_Id', $divisionId)
            ->get()
            ->groupBy('id');

        $map = [];
        foreach ($bouts as $boutId => $rows) {
            /** @var BaseCollection<int, Bout> $rows */
            if ($rows->count() < 2) {
                continue;
            }
            $first = $rows->first();
            $round = (int) $first->round;
            $bracketId = (int) $first->Bracket_Id;
            $wids = $rows->pluck('Wrestler_Id')->map(fn ($v) => (int) $v)->sort()->values()->all();
            if (count($wids) < 2) {
                continue;
            }
            $display = (string) ($first->bout_number ?? $first->id);
            $key = $this->boutKey($bracketId, $round, $wids[0], $wids[1]);
            $map[$key] = $display;
        }

        return $map;
    }

    private function boutKey(int $bracketId, int $round, int $w1, int $w2): string
    {
        $lo = min($w1, $w2);
        $hi = max($w1, $w2);

        return $bracketId . '|' . $round . '|' . $lo . '|' . $hi;
    }

    private function lookupBoutDisplay(array $map, int $bracketId, int $round, int $id1, int $id2): ?string
    {
        $key = $this->boutKey($bracketId, $round, $id1, $id2);

        return $map[$key] ?? null;
    }

    /**
     * @param  list<array{name: string}>  $wrestlers
     */
    private function weightClassLabel(array $wrestlers, BaseCollection $twsById): string
    {
        $weights = [];
        foreach ($wrestlers as $w) {
            $tw = $twsById->get($w['id']);
            if ($tw && $tw->wr_weight !== null && $tw->wr_weight !== '') {
                $n = is_numeric($tw->wr_weight) ? (float) $tw->wr_weight : null;
                if ($n !== null) {
                    $weights[] = $n;
                }
            }
        }
        if ($weights === []) {
            return '—';
        }
        $min = min($weights);
        $max = max($weights);
        if (abs($min - $max) < 0.001) {
            return (string) $min;
        }

        $fmt = fn ($v) => ((float) (int) $v === $v) ? (string) (int) $v : (string) $v;

        return $fmt($min) . ' – ' . $fmt($max);
    }
}
