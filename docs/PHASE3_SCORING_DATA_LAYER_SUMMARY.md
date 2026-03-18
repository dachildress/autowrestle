# Phase 3: Scoring Data Layer – Summary

## Status: Complete

Phase 3 (Scoring Data Layer) from the mat-side plan is implemented. No UI is added in this phase; the layer is consumed by Phase 4 (scoring screen) and Phase 5 (endpoints/JS).

## What was delivered

### Migrations

- **`2025_03_15_100000_create_bout_scoring_state_table.php`** – Table `bout_scoring_state`: `tournament_id`, `bout_id`, `red_wrestler_id`, `green_wrestler_id`, `red_score`, `green_score`, `period`, `clock_seconds`, `status` (pending/live/paused/completed), `winner_id`, `result_type`, plus side timers (blood, injury, head_neck, recovery for red/green). Unique on `(tournament_id, bout_id)`.
- **`2025_03_15_100001_create_bout_scoring_events_table.php`** – Table `bout_scoring_events`: `tournament_id`, `bout_id`, `side` (red/green/neutral), `event_type`, `points`, `period`, `match_time_snapshot`, `note`, `created_by`. Audit trail for scoring events.
- **`2025_03_17_100000_add_completed_at_to_bout_scoring_state.php`** – `completed_at` on `bout_scoring_state`.
- **`2025_03_18_100000_add_display_swap_to_bout_scoring_state.php`** – `display_swap` boolean on `bout_scoring_state`.

### Models

- **`App\Models\BoutScoringState`** – BelongsTo Tournament, TournamentWrestler (red, green, winner); HasMany BoutScoringEvent (scoped by tournament_id/bout_id). Casts for scores, period, clock, timers, `display_swap`, `completed_at`. `isCompleted()`.
- **`App\Models\BoutScoringEvent`** – BelongsTo Bout (via bout_id), User (created_by). Used for audit trail.

### Service

- **`App\Services\MatScoringService`** – Uses `DivisionPeriodService` for period/clock defaults.
  - **getOrCreateState()** – Load or create `BoutScoringState` for a bout (red/green from bout rows).
  - **startBout()** – Set status to `live`.
  - **setStatus()** – pending / live / paused.
  - **recordEvent()** – Create `BoutScoringEvent` (side, event_type, points, period, match_time_snapshot, note, created_by).
  - **updateScores()** – Update red_score, green_score on state.
  - **setPeriod()** – Update period (and optional clock).
  - **setClock()** – Update clock_seconds.
  - **setSideTimer()** – Update a side-timer column (blood/injury/head_neck/recovery).
  - **completeBout()** – Set status completed, winner_id, result_type, completed_at; sync to `bouts` (completed, score) where applicable.
  - **resetBout()** – Clear state and optionally events for re-scoring.

All writes go through state and event tables; mat-side UI (Phase 4/5) and `MatBoutController` use this service.

## Dependencies

- **DivisionPeriodService** – Provides period length (e.g. for initial clock) from division/period settings.
- **Bout / TournamentWrestler** – Existing models; state and events reference them.

## Next (already implemented)

- **Phase 4** – Mat-Side Scoring Screen: `MatBoutController::show`, view `mat/bout-show.blade.php`.
- **Phase 5** – Endpoints and JS: clock, period, event, timer, comment, displaySwap, complete, reset in `MatBoutController`; front-end calls these and uses `MatScoringService`.
- **Phase 6** – History and Settings: `mat.bout.history`, `mat.settings` routes and views.

Phase 3 is complete. Proceed to Phase 4/5/6 enhancements or other initiatives as needed.
