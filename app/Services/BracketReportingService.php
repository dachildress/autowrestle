<?php

namespace App\Services;

use App\Models\Bout;
use App\Models\BoutScoringState;
use App\Models\Bracket;
use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use Illuminate\Support\Collection;

/**
 * Reporting layer for completed brackets and final placements.
 * Uses existing bout and bout_scoring_state data only; does not modify scoring workflow.
 */
class BracketReportingService
{
    /**
     * Check if every bout in the bracket has been completed (bout_scoring_state.status = 'completed').
     */
    public function isBracketComplete(int $tournamentId, int $bracketId): bool
    {
        $boutIds = Bout::where('Tournament_Id', $tournamentId)
            ->where('Bracket_Id', $bracketId)
            ->distinct()
            ->pluck('id');

        if ($boutIds->isEmpty()) {
            return false;
        }

        $completedCount = BoutScoringState::where('tournament_id', $tournamentId)
            ->whereIn('bout_id', $boutIds)
            ->where('status', 'completed')
            ->count();

        return $completedCount === $boutIds->count();
    }

    /**
     * Get final placements for a bracket (only valid if bracket is complete).
     * Returns array of [ 'place' => int, 'tournament_wrestler_id' => int, 'name' => string, 'club' => string, 'wins' => int ].
     * Place is 1-based; ties get the same place and the next place number skips (e.g. two 2nd → next is 4th).
     */
    public function getPlacementsForBracket(int $tournamentId, int $bracketId): array
    {
        $wrestlerIds = Bracket::where('Tournament_Id', $tournamentId)
            ->where('id', $bracketId)
            ->pluck('wr_Id')
            ->unique()
            ->values();

        if ($wrestlerIds->isEmpty()) {
            return [];
        }

        $boutIds = Bout::where('Tournament_Id', $tournamentId)
            ->where('Bracket_Id', $bracketId)
            ->distinct()
            ->pluck('id');

        $completedStates = BoutScoringState::where('tournament_id', $tournamentId)
            ->whereIn('bout_id', $boutIds)
            ->where('status', 'completed')
            ->get();

        $wins = array_fill_keys($wrestlerIds->all(), 0);
        foreach ($completedStates as $state) {
            if ($state->winner_id !== null && isset($wins[$state->winner_id])) {
                $wins[$state->winner_id]++;
            }
        }

        $sorted = collect($wins)->sortDesc()->keys()->values()->all();
        $wrestlers = TournamentWrestler::whereIn('id', $wrestlerIds)
            ->get()
            ->keyBy('id');

        $place = 1;
        $result = [];
        $prevWins = null;
        $placeIncrement = 1;
        foreach ($sorted as $wrId) {
            $w = $wins[$wrId];
            if ($prevWins !== null && $w < $prevWins) {
                $place = $placeIncrement;
            }
            $prevWins = $w;
            $tw = $wrestlers->get($wrId);
            $result[] = [
                'place' => $place,
                'tournament_wrestler_id' => $wrId,
                'name' => $tw ? trim($tw->wr_first_name . ' ' . $tw->wr_last_name) : '—',
                'club' => $tw ? ($tw->wr_club ?? '—') : '—',
                'wins' => $w,
            ];
            $placeIncrement++;
        }

        return $result;
    }

    /**
     * Latest completed_at among all bouts in this bracket (from bout_scoring_state).
     */
    public function getBracketCompletedAt(int $tournamentId, int $bracketId): ?\DateTimeInterface
    {
        $boutIds = Bout::where('Tournament_Id', $tournamentId)
            ->where('Bracket_Id', $bracketId)
            ->distinct()
            ->pluck('id');

        if ($boutIds->isEmpty()) {
            return null;
        }

        return BoutScoringState::where('tournament_id', $tournamentId)
            ->whereIn('bout_id', $boutIds)
            ->where('status', 'completed')
            ->max('completed_at');
    }

    /**
     * Get bracket metadata: division, group (from first wrestler), tournament, wrestler count.
     */
    public function getBracketMeta(int $tournamentId, int $bracketId): ?array
    {
        $bracketRows = Bracket::where('Tournament_Id', $tournamentId)
            ->where('id', $bracketId)
            ->get();

        if ($bracketRows->isEmpty()) {
            return null;
        }

        $first = $bracketRows->first();
        $division = Division::where('id', $first->Division_Id)->where('Tournament_Id', $tournamentId)->first();
        $tournament = Tournament::find($tournamentId);
        $group = null;
        $groupId = TournamentWrestler::where('id', $first->wr_Id)->where('Tournament_id', $tournamentId)->value('group_id');
        if ($groupId !== null) {
            $group = DivGroup::where('id', $groupId)
                ->where('Tournament_Id', $tournamentId)
                ->where('Division_id', $first->Division_Id)
                ->first();
        }

        $wrestlerIds = $bracketRows->pluck('wr_Id')->unique()->values();
        $weightRange = TournamentWrestler::where('Tournament_id', $tournamentId)
            ->whereIn('id', $wrestlerIds)
            ->selectRaw('MIN(COALESCE(wr_weight, 0)) as low, MAX(COALESCE(wr_weight, 0)) as high')
            ->first();
        $weightMin = $weightRange && $weightRange->low > 0 ? (int) $weightRange->low : null;
        $weightMax = $weightRange && $weightRange->high > 0 ? (int) $weightRange->high : null;

        return [
            'tournament_id' => $tournamentId,
            'tournament_name' => $tournament ? $tournament->TournamentName : '—',
            'tournament_date' => $tournament ? $tournament->TournamentDate : null,
            'division_id' => $first->Division_Id,
            'division_name' => $division ? $division->DivisionName : '—',
            'group_id' => $groupId,
            'group_name' => $group ? $group->Name : '—',
            'bracket_id' => $bracketId,
            'wrestler_count' => $bracketRows->count(),
            'weight_min' => $weightMin,
            'weight_max' => $weightMax,
        ];
    }

    /**
     * List of all bracket summaries for a tournament (started or not, completed or not).
     * Same shape as getCompletedBracketSummaries; completed_at/champion/placements are null/empty when bracket is not complete.
     */
    public function getAllBracketSummaries(int $tournamentId): Collection
    {
        $bracketIds = Bracket::where('Tournament_Id', $tournamentId)
            ->select('brackets.id as bracket_id', 'brackets.Tournament_Id', 'brackets.Division_Id')
            ->distinct()
            ->get();

        $out = collect();
        foreach ($bracketIds as $row) {
            $tid = (int) $row->Tournament_Id;
            $bid = (int) $row->bracket_id;
            $meta = $this->getBracketMeta($tid, $bid);
            if ($meta === null) {
                continue;
            }
            $completedAt = null;
            $champion = null;
            $placements = [];
            if ($this->isBracketComplete($tid, $bid)) {
                $completedAt = $this->getBracketCompletedAt($tid, $bid);
                $placements = $this->getPlacementsForBracket($tid, $bid);
                foreach ($placements as $p) {
                    if ($p['place'] === 1) {
                        $champion = $p['name'];
                        break;
                    }
                }
            }
            $out->push([
                'bracket_id' => $bid,
                'tournament_id' => $tid,
                'tournament_name' => $meta['tournament_name'],
                'tournament_date' => $meta['tournament_date'],
                'division_name' => $meta['division_name'],
                'group_name' => $meta['group_name'],
                'group_id' => $meta['group_id'],
                'division_id' => $meta['division_id'],
                'wrestler_count' => $meta['wrestler_count'],
                'weight_min' => $meta['weight_min'] ?? null,
                'weight_max' => $meta['weight_max'] ?? null,
                'completed_at' => $completedAt,
                'champion' => $champion,
                'placements' => $placements,
            ]);
        }

        return $out;
    }

    /**
     * List of completed bracket ids for a tournament (optionally filtered by division or group).
     * Returns array of [ 'bracket_id' => int, 'tournament_id' => int, ...meta ].
     */
    public function getCompletedBracketSummaries(
        ?int $tournamentId = null,
        ?int $divisionId = null,
        ?int $groupId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection {
        $bracketIds = Bracket::query()
            ->select('brackets.id as bracket_id', 'brackets.Tournament_Id', 'brackets.Division_Id')
            ->when($tournamentId !== null, fn ($q) => $q->where('brackets.Tournament_Id', $tournamentId))
            ->when($divisionId !== null, fn ($q) => $q->where('brackets.Division_Id', $divisionId))
            ->distinct()
            ->get();

        $out = collect();
        foreach ($bracketIds as $row) {
            $tid = (int) $row->Tournament_Id;
            $bid = (int) $row->bracket_id;
            if (! $this->isBracketComplete($tid, $bid)) {
                continue;
            }
            $meta = $this->getBracketMeta($tid, $bid);
            if ($meta === null) {
                continue;
            }
            if ($groupId !== null && (int) $meta['group_id'] !== $groupId) {
                continue;
            }
            $completedAt = $this->getBracketCompletedAt($tid, $bid);
            if ($dateFrom !== null && $completedAt !== null) {
                $d = $completedAt instanceof \DateTimeInterface ? $completedAt->format('Y-m-d') : $completedAt;
                if ($d < $dateFrom) {
                    continue;
                }
            }
            if ($dateTo !== null && $completedAt !== null) {
                $d = $completedAt instanceof \DateTimeInterface ? $completedAt->format('Y-m-d') : $completedAt;
                if ($d > $dateTo) {
                    continue;
                }
            }
            $placements = $this->getPlacementsForBracket($tid, $bid);
            $champion = null;
            foreach ($placements as $p) {
                if ($p['place'] === 1) {
                    $champion = $p['name'];
                    break;
                }
            }
            $out->push([
                'bracket_id' => $bid,
                'tournament_id' => $tid,
                'tournament_name' => $meta['tournament_name'],
                'tournament_date' => $meta['tournament_date'],
                'division_name' => $meta['division_name'],
                'group_name' => $meta['group_name'],
                'group_id' => $meta['group_id'],
                'division_id' => $meta['division_id'],
                'wrestler_count' => $meta['wrestler_count'],
                'completed_at' => $completedAt,
                'champion' => $champion,
                'placements' => $placements,
            ]);
        }

        return $out;
    }

    /**
     * Get all completed bracket results for a specific group (by tournament + group id).
     */
    public function getCompletedBracketsForGroup(int $tournamentId, int $groupId): Collection
    {
        return $this->getCompletedBracketSummaries($tournamentId, null, $groupId);
    }

    /**
     * Get placement history for a tournament wrestler: all completed brackets they are in and their place.
     */
    public function getWrestlerPlacementHistory(int $tournamentWrestlerId): array
    {
        $tw = TournamentWrestler::find($tournamentWrestlerId);
        if (! $tw) {
            return [];
        }

        $bracketIds = Bracket::where('Tournament_Id', $tw->Tournament_id)
            ->where('wr_Id', $tournamentWrestlerId)
            ->distinct()
            ->pluck('id');

        $result = [];
        foreach ($bracketIds as $bid) {
            if (! $this->isBracketComplete($tw->Tournament_id, $bid)) {
                continue;
            }
            $meta = $this->getBracketMeta($tw->Tournament_id, $bid);
            $placements = $this->getPlacementsForBracket($tw->Tournament_id, $bid);
            $place = null;
            foreach ($placements as $p) {
                if ($p['tournament_wrestler_id'] == $tournamentWrestlerId) {
                    $place = $p['place'];
                    break;
                }
            }
            if ($place === null) {
                continue;
            }
            $result[] = [
                'tournament_id' => $tw->Tournament_id,
                'tournament_name' => $meta['tournament_name'] ?? '—',
                'tournament_date' => $meta['tournament_date'] ?? null,
                'division_name' => $meta['division_name'] ?? '—',
                'group_name' => $meta['group_name'] ?? '—',
                'bracket_id' => $bid,
                'place' => $place,
                'wrestler_count' => $meta['wrestler_count'] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get bouts for a bracket for public bracket display (including not-yet-wrestled matchups).
     * Returns array of bout cards: bout_id, round, result_label, time_display, red_name, green_name, etc.
     * For completed bouts: full result; for scheduled but not wrestled: result_label/time_display as "—".
     */
    public function getBoutsForBracket(int $tournamentId, int $bracketId): array
    {
        $boutIds = Bout::where('Tournament_Id', $tournamentId)
            ->where('Bracket_Id', $bracketId)
            ->distinct()
            ->pluck('id');

        if ($boutIds->isEmpty()) {
            return [];
        }

        $states = BoutScoringState::where('tournament_id', $tournamentId)
            ->whereIn('bout_id', $boutIds)
            ->where('status', 'completed')
            ->get()
            ->keyBy('bout_id');

        $boutRows = Bout::where('Tournament_Id', $tournamentId)
            ->whereIn('id', $boutIds)
            ->orderBy('id')
            ->orderBy('Wrestler_Id')
            ->get();

        $wrestlerIds = $boutRows->pluck('Wrestler_Id')->unique()->filter()->values();
        $wrestlers = TournamentWrestler::where('Tournament_id', $tournamentId)
            ->whereIn('id', $wrestlerIds)
            ->get()
            ->keyBy('id');

        $boutsByBoutId = $boutRows->groupBy('id');
        $result = [];
        foreach ($boutIds as $bid) {
            $rows = $boutsByBoutId->get($bid);
            if (! $rows || $rows->count() < 2) {
                continue;
            }
            $firstRow = $rows->first();
            $round = $firstRow->round ?? 0;
            $state = $states->get($bid);

            if ($state) {
                $redId = $state->red_wrestler_id;
                $greenId = $state->green_wrestler_id;
                $red = $wrestlers->get($redId);
                $green = $wrestlers->get($greenId);
                $redName = $red ? trim($red->wr_first_name . ' ' . $red->wr_last_name) : '–';
                $greenName = $green ? trim($green->wr_first_name . ' ' . $green->wr_last_name) : '–';
                $redClub = $red ? ($red->wr_club ?? '') : '';
                $greenClub = $green ? ($green->wr_club ?? '') : '';
                $wrtime = $firstRow->wrtime ?? null;
                $pin = (bool) $firstRow->pin;
                $resultType = $state->result_type ? trim($state->result_type) : null;
                $isPin = $pin || (strtolower((string) $resultType) === 'pin');
                $isMajor = strtolower((string) $resultType) === 'major decision' || strtolower((string) $resultType) === 'major';
                $resultLabel = $this->boutResultLabel($resultType, $pin, $wrtime);
                $timeDisplay = $wrtime ?: ($isPin ? 'PINS' : 'F:00');
                $result[] = [
                    'bout_id' => $bid,
                    'bout_number' => $firstRow->bout_number,
                    'round' => $round,
                    'result_label' => $resultLabel,
                    'time_display' => $timeDisplay,
                    'is_pin' => $isPin,
                    'is_major' => $isMajor,
                    'red_name' => $redName,
                    'green_name' => $greenName,
                    'red_club' => $redClub,
                    'green_club' => $greenClub,
                    'red_wrestler_id' => $redId,
                    'green_wrestler_id' => $greenId,
                    'red_score' => (int) $state->red_score,
                    'green_score' => (int) $state->green_score,
                    'winner_id' => $state->winner_id,
                ];
            } else {
                $wrestlerIdsForBout = $rows->pluck('Wrestler_Id')->unique()->values();
                $redId = $wrestlerIdsForBout->get(0);
                $greenId = $wrestlerIdsForBout->get(1);
                $red = $wrestlers->get($redId);
                $green = $wrestlers->get($greenId);
                $redName = $red ? trim($red->wr_first_name . ' ' . $red->wr_last_name) : '–';
                $greenName = $green ? trim($green->wr_first_name . ' ' . $green->wr_last_name) : '–';
                $redClub = $red ? ($red->wr_club ?? '') : '';
                $greenClub = $green ? ($green->wr_club ?? '') : '';
                $result[] = [
                    'bout_id' => $bid,
                    'bout_number' => $firstRow->bout_number,
                    'round' => $round,
                    'result_label' => '—',
                    'time_display' => '—',
                    'is_pin' => false,
                    'is_major' => false,
                    'red_name' => $redName,
                    'green_name' => $greenName,
                    'red_club' => $redClub,
                    'green_club' => $greenClub,
                    'red_wrestler_id' => $redId,
                    'green_wrestler_id' => $greenId,
                    'red_score' => 0,
                    'green_score' => 0,
                    'winner_id' => null,
                ];
            }
        }

        usort($result, function ($a, $b) {
            if ($a['round'] !== $b['round']) {
                return $a['round'] <=> $b['round'];
            }
            return $a['bout_id'] <=> $b['bout_id'];
        });

        return $result;
    }

    private function boutResultLabel(?string $resultType, bool $pin, ?string $wrtime): string
    {
        if ($pin || (strtolower((string) $resultType) === 'pin')) {
            return 'PINS';
        }
        $r = strtolower((string) $resultType);
        if ($r === 'major decision' || $r === 'major') {
            return 'MD';
        }
        if ($r === 'technical fall' || $r === 'tech fall') {
            return 'TF';
        }
        if ($r === 'forfeit' || $r === 'default' || $r === 'disqualification' || $r === 'double forfeit') {
            return $resultType ?: 'FFT';
        }
        if ($wrtime) {
            return 'F:00';
        }
        return 'F:00';
    }
}
