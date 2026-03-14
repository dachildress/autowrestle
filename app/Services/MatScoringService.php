<?php

namespace App\Services;

use App\Models\Bout;
use App\Models\BoutScoringEvent;
use App\Models\BoutScoringState;
use App\Services\DivisionPeriodService;

class MatScoringService
{
    public function __construct(
        private DivisionPeriodService $periodService
    ) {}

    /**
     * Get or create scoring state for a bout. Red/green wrestler ids are from the bout's two rows (order by Wrestler_Id).
     * When creating a new state, use initialClockSeconds (e.g. division Period 1 duration) if provided.
     */
    public function getOrCreateState(
        int $tournamentId,
        int $boutId,
        int $redWrestlerId,
        int $greenWrestlerId,
        ?int $initialClockSeconds = null
    ): BoutScoringState {
        $state = BoutScoringState::where('tournament_id', $tournamentId)
            ->where('bout_id', $boutId)
            ->first();

        if ($state) {
            return $state;
        }

        return BoutScoringState::create([
            'tournament_id' => $tournamentId,
            'bout_id' => $boutId,
            'red_wrestler_id' => $redWrestlerId,
            'green_wrestler_id' => $greenWrestlerId,
            'red_score' => 0,
            'green_score' => 0,
            'period' => 1,
            'clock_seconds' => $initialClockSeconds !== null ? max(0, $initialClockSeconds) : 0,
            'status' => 'pending',
        ]);
    }

    /**
     * Set status to live (start bout).
     */
    public function startBout(BoutScoringState $state): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $state->update(['status' => 'live']);
    }

    /**
     * Pause or resume clock.
     */
    public function setStatus(BoutScoringState $state, string $status): void
    {
        if ($state->isCompleted()) {
            return;
        }
        if (!in_array($status, ['pending', 'live', 'paused'], true)) {
            return;
        }
        $state->update(['status' => $status]);
    }

    /**
     * Record a scoring event or comment (audit trail).
     */
    public function recordEvent(
        int $tournamentId,
        int $boutId,
        string $side,
        string $eventType,
        int $points = 0,
        ?int $period = null,
        ?int $matchTimeSnapshot = null,
        ?string $note = null,
        ?int $createdBy = null
    ): BoutScoringEvent {
        return BoutScoringEvent::create([
            'tournament_id' => $tournamentId,
            'bout_id' => $boutId,
            'side' => $side,
            'event_type' => $eventType,
            'points' => $points,
            'period' => $period,
            'match_time_snapshot' => $matchTimeSnapshot,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Update red/green scores on state.
     */
    public function updateScores(BoutScoringState $state, int $redScore, int $greenScore): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $state->update([
            'red_score' => max(0, $redScore),
            'green_score' => max(0, $greenScore),
        ]);
    }

    /**
     * Set current period (1-6) and optionally set clock to the given duration.
     */
    public function setPeriod(BoutScoringState $state, int $period, ?int $clockSeconds = null): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $updates = ['period' => max(1, min(6, $period))];
        if ($clockSeconds !== null) {
            $updates['clock_seconds'] = max(0, $clockSeconds);
        }
        $state->update($updates);
    }

    /**
     * Set main clock remaining seconds.
     */
    public function setClock(BoutScoringState $state, int $clockSeconds): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $state->update(['clock_seconds' => max(0, $clockSeconds)]);
    }

    /**
     * Set a side timer (e.g. blood, injury). Keys: blood_time_red, injury_time_green, etc.
     */
    public function setSideTimer(BoutScoringState $state, string $column, int $seconds): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $allowed = [
            'blood_time_red', 'blood_time_green',
            'injury_time_red', 'injury_time_green',
            'head_neck_time_red', 'head_neck_time_green',
            'recovery_time_red', 'recovery_time_green',
        ];
        if (!in_array($column, $allowed, true)) {
            return;
        }
        $state->update([$column => max(0, $seconds)]);
    }

    /**
     * Mark bout as completed with winner, result type, and optional match end time.
     * Also sets completed = true on the bouts table so projection and match list stay in sync.
     */
    public function completeBout(BoutScoringState $state, ?int $winnerId, ?string $resultType = null, ?\DateTimeInterface $completedAt = null): void
    {
        $state->update([
            'status' => 'completed',
            'winner_id' => $winnerId,
            'result_type' => $resultType,
            'completed_at' => $completedAt,
        ]);

        Bout::where('Tournament_Id', $state->tournament_id)
            ->where('id', $state->bout_id)
            ->update(['completed' => true]);
    }

    /**
     * Reset state to initial values (clock, period, scores, timers). Does not delete events.
     * If divisionId is provided, clock_seconds is set to that division's Period 1 duration; otherwise 0.
     */
    public function resetBout(BoutScoringState $state, ?int $divisionId = null): void
    {
        if ($state->isCompleted()) {
            return;
        }
        $clockSeconds = 0;
        if ($divisionId !== null) {
            $clockSeconds = $this->periodService->getPeriodDuration($divisionId, '1');
        }
        $state->update([
            'red_score' => 0,
            'green_score' => 0,
            'period' => 1,
            'clock_seconds' => $clockSeconds,
            'status' => 'pending',
            'blood_time_red' => 300,
            'blood_time_green' => 300,
            'injury_time_red' => 90,
            'injury_time_green' => 90,
            'head_neck_time_red' => 300,
            'head_neck_time_green' => 300,
            'recovery_time_red' => 120,
            'recovery_time_green' => 120,
        ]);
    }
}
