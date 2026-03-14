# AutoWrestle – AI Context for New Development Box

Use this file when developing on a **fresh copy** of the project (no prior code). It describes the app, conventions, and **features not yet implemented**.

---

## 1. Project overview

**AutoWrestle** is a Laravel (Blade, server-rendered) app for wrestling tournament management and **mat-side scoring**.

- **Public:** Tournament list, registration (add/withdraw wrestlers), my bouts (wrestler/parent view), wrestler CRUD.
- **Manage (admin):** Tournaments, divisions, groups, brackets, bouts, check-in, scoring (legacy manage flow), projection views, mats, scorers.
- **Mat-side (scorer):** Match list, live bout scoring (clock, period, points, timers, results), **virtual audience display** (spectator scoreboard).

Legacy column names (PascalCase, e.g. `Tournament_Id`, `Wrestler_Id`) are kept in the DB and in Eloquent `$fillable`/attributes.

---

## 2. Tech stack

- **PHP / Laravel** (Blade views, no Inertia/SPA for main flows).
- **MySQL** (migrations in `database/migrations/`).
- **Auth:** Laravel Fortify; `users` table with legacy columns: `accesslevel`, `active`, `username`, `mycode`, `Tournament_id`, `mat_number`.
- **Access levels:** `accesslevel` — `'0'` = admin, `'5'` = scorer (mat-side), `'10'` = normal user. Scorers have `mat_number` set (which mat they run).

---

## 3. Key structure

```
app/
  Http/Controllers/
    MatDashboardController.php   # Match list (mat-side)
    MatBoutController.php        # Bout show, state, clock, period, event, comment, timer, displaySwap, complete, reset, results
    MatVirtualController.php     # Virtual settings, display, currentState (JSON for polling)
    MatSettingsController.php
    TournamentController.php     # Public tournaments, my bouts
    RegistrationController.php
    WrestlerController.php
    Manage/                      # All manage.* routes (tournaments, divisions, groups, brackets, bouts, check-in, scoring, projection, mats, scorers)
  Models/
    User.php                     # isAdmin(), isScorer() (accesslevel 0, 5)
    Bout.php, Bracket.php, TournamentWrestler.php, Wrestler.php, Division.php, DivGroup.php, ...
    BoutScoringState.php         # Live scoring state (red/green score, period, clock_seconds, status, display_swap, completed_at, ...)
    BoutScoringEvent.php         # Audit trail of scoring events
    DivisionPeriodSetting.php    # Per-division period durations (1,2,3, OT1, OT2, OT3)
  Services/
    MatScoringService.php        # getOrCreateState, startBout, setStatus, setClock, recordEvent, setPeriod, setSideTimer, completeBout, resetBout
    DivisionPeriodService.php   # Period durations by division, getNextPeriod, isTiedAfterOT3

resources/views/
  layouts/autowrestle.blade.php  # Main layout; mat.* routes yield content without container
  mat/
    dashboard.blade.php          # Match list (bouts on scorer's mat, exclude completed)
    bout-show.blade.php          # Scoring UI (red/green panels, clock, period, events, timers, results, Virtual link)
    bout-results.blade.php       # Result form (winner, result type, time); POST → redirect to match list
    bout-history.blade.php
    nav.blade.php                # Mat nav (Match list, Scoring, Summary, Results, Virtual, Settings, Logout)
    virtual/
      settings.blade.php         # Layout, font size (px), Display button
      display.blade.php          # Audience scoreboard (bout #, timer, period, red left / green right, names + score boxes)
  manage/                        # Tournament management views

routes/
  autowrestle.php                # Public + mat routes
  autowrestle_manage.php         # manage.* routes (prefix tournaments/manage)
```

---

## 4. Mat-side scoring flow

- **Dashboard:** `GET /mat` → list of bouts on scorer’s mat (from `bouts` where `mat_number` = user’s mat), excluding bouts with scoring state `status = 'completed'` (or `bouts.completed = true`). Session stores `mat_current_bout_id` when opening a bout.
- **Bout show:** `GET /mat/bout/{boutId}` — scoring page with red/green panels; each panel has wrestler name, side dropdown (red/green), score, point buttons (data-delta: +1, -1, +2, etc.), timers (blood, injury, head/neck, recovery). Center: clock, period dropdown, Start/Stop/Set/Reset, comment, **Sound Horn**, complete bout (winner, result type), Reset Bout.
- **APIs (POST):** state, clock, period, event, comment, timer, **display-swap** (body: `display_swap` boolean), complete. All require scorer auth and bout on scorer’s mat.
- **Results:** `GET/POST /mat/bout/{boutId}/results` — set winner, result type, match end time; on save, set `bouts.completed = true` and scoring state `completed_at`, then redirect to match list.
- **Virtual link:** Opens in a **popup window** (not tab) via `window.open(..., 'virtual', '...')`; script in layout binds `a[data-virtual-url]` to open popup.

---

## 5. Virtual audience display

- **Settings:** `GET /mat/virtual` — layout dropdown (e.g. Folkstyle), font size (px, default 84), Display button → navigates same window to display URL with `layout` and `font` query params.
- **Display:** `GET /mat/virtual/display` — full-screen scoreboard. **Red is always left, green right.** Score boxes show `red_score` and `green_score` in place; **only wrestler names swap** when scorer changes side (display_swap). Polls `GET /mat/virtual/current-state` every 500 ms (cache-busting, `Cache-Control: no-store`). Response includes `bout_id`, `red_score`, `green_score`, `period`, `clock_seconds`, `display_swap`, `red_name`, `red_team`, `green_name`, `green_team`.
- **Layout:** Bout number at top; then row: left column (name + red score box), center (timer, period), right column (name + green score box). Score boxes sized for two-digit numbers (min-width ~4.5em), large font for spectators (e.g. 3.5em). Content aligned top (`justify-content: flex-start` on body).

---

## 6. Database (scoring and key tables)

- **bout_scoring_state:** `tournament_id`, `bout_id`, `red_wrestler_id`, `green_wrestler_id`, `red_score`, `green_score`, `period`, `clock_seconds`, `status` (pending/live/paused/completed), `display_swap` (boolean), `completed_at`, plus side timers (blood_time_red/green, injury_time_*, etc.). Migrations: `2025_03_15_100000_create_bout_scoring_state_table`, `2025_03_17_100000_add_completed_at_...`, `2025_03_18_100000_add_display_swap_...`.
- **bout_scoring_events:** Audit log (tournament_id, bout_id, side, event_type, points, note, etc.).
- **bouts:** Two rows per match (Wrestler_Id per competitor); `mat_number`, `completed` (boolean). `completed` set when result is saved.
- **division_period_settings:** Per-division period lengths (period 1, 2, 3, OT1, OT2, OT3) for mat-side clock and period advancement.
- **users:** `mat_number` for scorers; `Tournament_id` for current tournament context.

---

## 7. Conventions

- **Authorization:** Mat bout endpoints use “bout on scorer’s mat” check (same tournament, same mat_number). Virtual and mat routes require `user->isScorer()`.
- **State JSON:** `MatBoutController::stateToArray(BoutScoringState)` returns status, display_swap, scores, period, clock, timers, events. Used after clock/period/event/timer/displaySwap/complete.
- **Division period timing:** `DivisionPeriodService` used for initial clock, period advance, and OT rules; admin configures per division in Manage → Division → Period settings.

---

## 8. Features NOT yet developed (implement when needed)

- **Sound Horn:** The “Sound Horn” button exists on the scoring page (`bout-show.blade.php`); the click handler is a placeholder (`/* placeholder */`). Implement: play an audio cue (e.g. short beep) when the scorer clicks it (browser Audio API or asset). Optionally respect a “Sound / horn on event” setting from mat settings if that is wired up.
- **Team sheet print:** REBUILD_NOTES.md lists “Team sheet / scan sheet prints” as TODO. Scan sheet print route exists (`manage.scansheet.print`); **team sheet** print (e.g. by team/club) is not implemented.
- **Mat view (public):** Plan mentions “Mat view (`/matview/{matid}`)” for public. No route or controller exists yet; add if a public mat view is required.
- **Team request / new team:** `TeamRequest` model and `teamrequest` table exist; registration or other flows for “request new club” may be incomplete. Verify and add UI/routes as needed.
- **About / Contact pages:** Listed in rebuild plan as static pages; not implemented.
- **Legacy password hashes:** Some users may have MySQL-style hashes; Laravel uses bcrypt. Plan: migration path or “reset password” for those users (see REBUILD_NOTES.md).
- **Virtual layout options:** Settings pass `layout` (e.g. Folkstyle) to display; the display view currently uses a single layout. If multiple layouts (e.g. different arrangements) are desired, implement alternate Blade partials or layout logic based on `layout` param.

---

## 9. Files to reference first

- **Mat routes and controllers:** `routes/autowrestle.php`, `MatBoutController`, `MatVirtualController`, `MatDashboardController`.
- **Scoring logic:** `MatScoringService`, `DivisionPeriodService`, `BoutScoringState`, `BoutScoringEvent`.
- **Virtual display:** `resources/views/mat/virtual/display.blade.php`, `MatVirtualController::display`, `MatVirtualController::currentState`.
- **Rebuild context:** `REBUILD_PLAN.md`, `REBUILD_NOTES.md`.

---

*This file is for Cursor (or other AI) when onboarding to a new development box with no prior code context.*
