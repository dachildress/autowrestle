# Phase 0: Mat-Side Discovery, Image Review, and Plan

## 1. Image Review (matsideimages folder)

**Location:** `C:\xampp\htdocs\dev\matsideimages`

**Files reviewed:** `matches.png`, `matchscreen.png`, `summary.png`, `virtual.png`

---

### 1.1 Match List (`matches.png`)

- **Layout:** Left vertical nav + main content (title, filter bar, match list table).
- **Left nav (legacy):** CONFIGS, SETTINGS, EVENTS, **MATCHES** (selected), MATCH, RESULT, SUMMARY, HISTORY, CHAT, HORN.
- **Title:** "Match List" (bold blue).
- **Filters (top bar):**
  - **Mat** – dropdown (e.g. "Mat 2").
  - **Weight class** – text/dropdown.
  - **Bout no** – text input.
  - **Match id** – text input.
  - **Incomplete Only** – dropdown.
  - **Search** – button to apply filters.
- **Match list (table):** One logical row per bout. Columns/display:
  - **Bout** – clickable link (e.g. "Bout 8", "Bout 14") → opens scoring screen.
  - **Division/weight** – e.g. "High School 103LBS", "High School 112".
  - **Round** – e.g. "Champ. Round 1".
  - **Wrestlers and teams** – Wrestler 1 (Team1), Wrestler 2 (Team2); each wrestler can be on its own line in the cell.
- **Styling:** Alternating row colors (white / light lavender); blue links.

---

### 1.2 Mat-Side Scoring Screen (`matchscreen.png`)

- **Layout:** Three columns – left nav, **center** (clock/controls), **left panel (Red)**, **right panel (Green)**. (In practice: left nav, then Red | Center | Green.)
- **Left nav:** CONFIGS, SETTINGS, SEASONS, MATCHES, ATHLETES, MATCH, RESULT, SUMMARY, HISTORY, HELP, VIRTUAL, OVERLAY, COMM, WOW! (version "v5.2" at bottom).
- **Center panel:**
  - **Bout header:** "Bout 0 -".
  - **Period:** &lt;==Prev | dropdown "Period 1" | Next==&gt;.
  - **Main clock:** Large display "2 00 00" (red on dark); label "Clock" with up/down/refresh; buttons **Start** (green), **Stop** (red), **Set**, **Reset**.
  - **Scores:** Two large "0"s; between them, two sets of four small boxes (red/white) – warnings/cautions/penalties.
  - **Comment:** Text input + **Add Comment** button.
  - **Sound Horn** button.
  - **Blink Status** dropdown.
  - **Reset Bout** button.
- **Red panel (left wrestler):**
  - Wrestler identity: "Unknown Unattached" (red).
  - Dropdowns: stance (e.g. "neutral"), side (e.g. "red").
  - **Scoring buttons** – each row: minus button, label with abbreviation and pts, plus button. Visible: **Caution (Ca) (0 pts)**, **Misconduct (MC) (0 pts)**, **Penalty 1 (P1) (1 pts)**, **Penalty 2 (P2) (2 pts)**, **Stalling (SW) (0 pts)**, **Takedown 3 (T3) (3 pts)**.
  - **Timers** (each with display + Start/Stop/Set/Reset): **Blood Time** (5:00:00), **Injury Time** (1:30:00), **Head/Neck** (5:00:00), **Recovery** (2:00:00).
- **Green panel (right):** Mirror of red – same buttons (Caution, Misconduct, Penalty 1/2, Stalling, Takedown 3) and same four timers (Blood, Injury, Head/Neck, Recovery).

---

### 1.3 Summary / History (`summary.png`)

- **Layout:** Left nav + main content (bout header, then period-by-period blocks).
- **Left nav:** CONFIGS, SETTINGS, EVENTS, MATCHES, MATCH, RESULT, **SUMMARY** (highlighted), HISTORY, CHAT, HORN, HELP, LOGOUT.
- **Bout header:** Division/weight and bout # (e.g. "High School - 103LBS (Bout 8)"); below, Red wrestler + club (red), Green wrestler + club (green).
- **Content:** Events grouped by **Period** and **Choice** (e.g. "Period 1", "Choice 1", "Period 2", "Choice 2", "Period 3").
- **Within each block:** Two columns – red wrestler events (left, red text), green wrestler events (right, green text). Each event: **type + (time)** e.g. "Takedown (1:07)", "Reversal (0:47)". Comments allowed (e.g. "red looks tired (0:24)").
- **Scores:** Cumulative score for Red and Green shown at bottom of each period/choice block.
- **Event types visible:** Takedown, Reversal, 2 Nearfall, Escape, Defer, Bottom; plus free-text comment.

---

### 1.4 Settings / Display (`virtual.png`)

- **Header:** "Settings" (dark bar, white text).
- **Fields:**
  - **Layout:** Dropdown – "select a layout".
  - **Font Size:** Input (e.g. "80") + unit "px" (with dropdown).
  - **Matside Server:** Dropdown – "No" (implies Yes/No for external display server).
  - **IP Address:** Placeholder "ip address" (conditional / when Matside Server enabled).
- **Button:** **Display** (apply and update display).

---

### 1.5 Workflow and Terminology (from images)

| Step | Screen | Action |
|------|--------|--------|
| 1 | Match List | Filter by mat, weight, bout no, match id, incomplete; Search; click Bout link. |
| 2 | Mat-Side Scoring (MATCH) | Run clock, use Red/Green buttons (caution, penalty, stalling, takedown, etc.), run side timers, Add Comment, Sound Horn, Reset Bout; period Prev/Next. |
| 3 | Result | (Referenced in nav; likely set winner/result type.) |
| 4 | Summary | View period/choice event history and cumulative scores. |
| 5 | History | (Referenced in nav; likely full event log.) |
| 6 | Settings | Layout, font size, Matside Server, IP; Display to apply. |

**Left nav (consolidated):** CONFIGS, SETTINGS, EVENTS, MATCHES, MATCH, RESULT, SUMMARY, HISTORY, CHAT, HORN, (HELP, VIRTUAL, OVERLAY, COMM as in scoring screen). For rebuild, minimum: **MATCHES** (list), **MATCH** (scoring), **RESULT**, **SUMMARY**, **HISTORY**, **SETTINGS**. CHAT/HORN can be placeholders or minimal.

---

## 2. Current Authentication System

| Item | Details |
|------|---------|
| **Stack** | Laravel Fortify (login, register, password reset, 2FA optional) |
| **Guard** | `web` (session) |
| **Login** | **Email + password** (Fortify `config/fortify.php`: `'username' => 'email'`). Login form is Blade: `resources/views/auth/login.blade.php`, POST to `/login`. |
| **Model** | `App\Models\User` |
| **Passwords** | Laravel hashed (User cast: `'password' => 'hashed'`) |
| **Post-login** | Fortify `config/fortify.php` `'home' => '/'` (no role-based redirect) |

---

## 3. User Model / Users Table

**Base migration** (`0001_01_01_000000_create_users_table.php`): `id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `timestamps`.

**Legacy add-on** (`2025_03_13_100000_add_legacy_columns_to_users_table.php`):
- `last_name`, `phone_number`
- **`accesslevel`** (char 2, default `'10'`) – used in code: `'0'` = super admin
- **`active`** (char 1, default `'0'`)
- **`username`** (string 40, default `''`) – **column exists but not used for login** (Fortify uses `email`)
- `mycode`, **`Tournament_id`** (unsigned int, nullable, default 1)

**User model** (`App\Models\User`):
- Fillable includes: `name`, `last_name`, `email`, `password`, `phone_number`, `accesslevel`, `active`, `username`, `mycode`, `Tournament_id`
- `isAdmin()`: `return $this->accesslevel === '0'`
- `managedTournaments()`: BelongsToMany Tournament via `tournamentusers`

**Conclusion:** Same table and model can support “scorer” users. No dedicated roles table; `accesslevel` is the only role-like field. No `mat_number` on users yet.

---

## 4. Roles / Permissions

- **No** dedicated roles or permissions packages (e.g. Spatie).
- **Convention in code:**
  - **Admin:** `$user->isAdmin()` (accesslevel `'0'`) – can access any tournament manage area.
  - **Manager:** User in `tournamentusers` for a tournament – can manage that tournament (ManageScoreController, ManageBoutController, etc.).
- No “scorer” role or mat-scope yet; all manage routes assume admin or tournament manager.

---

## 5. Tournament, Event, Match, Bout, Bracket, Division, Athlete, Team, Mat

| Concept | Exists | Table / Model | Notes |
|--------|--------|----------------|-------|
| **Tournament** | Yes | `tournaments`, `Tournament` | TournamentName, TournamentDate, etc. |
| **Division** | Yes | `divisions`, `Division` | DivisionName, **StartingMat**, **TotalMats**, PerBracket, Tournament_Id |
| **DivGroup** | Yes | `divgroups`, `DivGroup` | Group within division (composite PK: id, Tournament_Id, Division_id) |
| **Bracket** | Yes | `brackets`, `Bracket` | (id, wr_Id, wr_pos), Division_Id, Tournament_Id – one bracket = one group |
| **Bout** | Yes | `bouts`, `Bout` | **Two rows per bout** (id, Wrestler_Id). Has **mat_number**, round, points, wrtime, pin, color, scored, Tournament_Id, Division_Id, **completed** |
| **TournamentWrestler** | Yes | `tournamentwrestlers`, `TournamentWrestler` | wr_first_name, wr_last_name, wr_club, wr_weight, group_id, Tournament_id, etc. (athlete per tournament) |
| **Match** | No | — | “Match” = Bout in this codebase |
| **Event** | No | — | No event/round table; round is on `bouts.round` |
| **Team/Club** | Yes | `wr_club` on TournamentWrestler; `clubs` table exists | |
| **Mat** | Implicit | No mat table | Mat = integer (mat_number on bouts). Division has StartingMat + TotalMats; bouts have mat_number. |

**Mat assignment:** Bouts have `mat_number`. Admins can move bouts between mats (ManageMatController). No user→mat assignment yet (needed for scorers).

---

## 6. Admin Area Structure

- **Prefix:** `tournaments/manage` (and `/{tid}/...` for tournament-scoped).
- **Middleware:** `auth`.
- **Authorization:** `authorizeTournament($request, $tid)`: allow if user is admin (`isAdmin()`) or in `tournamentusers` for that tournament.
- **Layout:** `resources/views/layouts/autowrestle.blade.php` – when `manage.*` and route has `tid`, nav shows Tournament / View / Bracket / Bout / Print dropdowns (View composer in AppServiceProvider).
- **Entry:** “Manage” from nav → `manage.tournaments.index` → list of tournaments (admin: all; others: managed only) → “Manage” → `manage.view.summary` (or tournament show). No separate “admin panel” URL; admin = same manage area with broader tournament list.

---

## 7. Result Entry / Scoring Support

- **ManageScoreController** (under `tournaments/manage/{tid}/`):
  - **index:** Form “Bout #” → POST to **show**.
  - **show:** Load bout by id, two wrestlers (TournamentWrestler), show form: points1, points2, wintype (Points/Fall/Forfeit/Disqualified/etc.), totaltime.
  - **update:** Update both bout rows: points, pin, wrtime, scored=1, **completed=true**.
- **Bout fields used for results:** `points`, `pin`, `wrtime`, `scored`, `completed`. No period, no clock, no event log. No red/green or mat-side UI; single form per bout.

---

## 8. Mat Assignment Support

- **Bouts:** `bouts.mat_number` – set by BoutGenerationService (by division’s StartingMat/TotalMats) and editable via “Change mat” (ManageMatController).
- **Users:** No mat assignment. `users.Tournament_id` exists but is legacy; no `mat_number` on users.

---

## 9. Route Structure

- **web.php:** `/`, dashboard (auth), `require autowrestle.php`, `require settings.php`.
- **autowrestle.php:** Public tournament list, show, mybout search; auth: wrestlers, tournament register. Then `require autowrestle_manage.php`.
- **autowrestle_manage.php:** `auth`, prefix `tournaments/manage`, name `manage.`. Index/show/edit per tournament; under `{tid}`: divisions, groups, bracket, bouts, mats, projection, score, viewgroups, checkin.
- **settings.php:** Auth: profile, password change (Blade), security (Inertia), etc.

**Scoring routes today:**  
`GET manage.scoring.index` = `tournaments/manage/{tid}/score`  
`POST manage.scoring.show` = same URL (bout number)  
`POST manage.scoring.update` = `tournaments/manage/{tid}/score/update`

No mat-scoped or scorer-specific routes yet.

---

## 10. Blade Layouts / Components

- **Main layout:** `layouts/autowrestle.blade.php` – one layout for public and manage; `.app-backend` when `manage.*`. Inline styles, nav with dropdowns when `manageNav` and `tournament` are set.
- **Auth:** Blade (e.g. `auth/login`, `auth/register`, `auth/change-password`). Fortify uses these for login/register; some features (e.g. reset password) use Inertia.
- **Convention:** `@extends('layouts.autowrestle')`, `@section('content')`, `@section('panel_title')` optional. No Blade components directory used heavily; tables and forms are inline in views.

---

## 11. Operator / Scorer Users

- **No** scorer-specific code. No mat-based login, no “scorer dashboard,” no restriction of bouts by user mat.  
- **Reuse:** Same `User` and `users` table; add scorer role (e.g. accesslevel or new column) and **mat_number** (and optionally tournament scope).

---

## 12. Summary: What Exists vs What’s Needed

| Area | Exists | Reuse / extend | Add |
|------|--------|-----------------|-----|
| Auth | Fortify, email login, User | Same guard, same User; add username login path for scorers (or keep email and add mat in session). | Scorer redirect after login; optional username-based login. |
| Users | users table, accesslevel, active, username, Tournament_id | Add **mat_number** (nullable), use accesslevel (e.g. `'5'` = scorer) or new column. | Scorer role flag; mat_number column if not present. |
| Roles | accesslevel only | Keep; add one value for “scorer” (e.g. `'5'`). | Document convention; admin = `'0'`, manager = in tournamentusers, scorer = e.g. `'5'`. |
| Bouts / mats | bouts.mat_number, division mats | Filter bouts by `mat_number` for scorer. | Scorer sees only bouts where `mat_number` = user’s mat. |
| Scoring UI | Simple form (points, wintype, time) | Keep for “admin/manager result entry.” | **New** mat-side screen (TrackWrestling-style): red/green, clock, buttons, timers, history. |
| Scoring data | bouts: points, pin, wrtime, scored, completed | Keep for final result. | **New** tables: scoring state (period, clock, timers, status), **event/history** (event type, points, side, time, created_by). |
| Admin | Manage under tournaments | Add “Scorer users” (list/create/edit, mat, password reset). | New controller + views under manage or under a dedicated admin scope. |
| Mat list | None for scorer | — | New: “mat dashboard” / match list for scorer (bouts for their mat). |

---

## 13. How the Image-Based Workflow Maps to the Codebase

Reference images: `C:\xampp\htdocs\dev\matsideimages` (matches.png, matchscreen.png, summary.png, virtual.png). Map as follows:

| Legacy screen | Current codebase | Action |
|---------------|------------------|--------|
| **Match list** | None | Add scorer match list: bouts for `auth()->user()->mat_number` (and tournament). Reuse Bout, TournamentWrestler, Division; filter by mat, optional filters. |
| **Mat-side scoring** | manage.scoring (bout # → form → save) | Add **new** mat-side scoring screen (separate route/controller/view). Reuse Bout, TournamentWrestler; add scoring state + event tables and wire UI (clock, buttons, timers, comments). |
| **Summary/history** | None | Add summary/history view for a bout; read from new event/history table. |
| **Settings/display** | None | Add simple settings view (or placeholder) for mat-side; reuse layout. |
| **Left nav** | manage nav (Tournament/View/Bracket/Bout/Print) | Scorer area: different nav (e.g. Match list, Scoring, Summary, Settings) when user is scorer; no tournament dropdown. |

Scorer flow: **Login → Match list → click Bout → mat-side scoring → Result / Summary / History; Settings for display.**  
Tournament context for scorer (e.g. `user.Tournament_id`) or chosen once (e.g. “active tournament” in session).

---

## 14. Phased Plan (Minimal Disruption)

### Phase 1 – Simple Scorer User Support
- **Migrations:** Add `mat_number` (nullable unsigned int) and optionally `role` or use `accesslevel` (e.g. `'5'` = scorer) on `users`. Ensure `username` is unique where used.
- **Model:** User: `mat_number` in fillable/casts; `isScorer()` (e.g. accesslevel === '5'); optionally `scorerTournament()` if Tournament_id used for scorer.
- **Auth:** Keep Fortify; add **optional** username login (Fortify config + custom AuthenticatedSessionController or callback) so scorers can log in with username. Or keep email login and require scorer to have email; then redirect by role.
- **Post-login:** Fortify `LoginResponse` or `Redirect::intended` override: if scorer, redirect to scorer mat dashboard (e.g. `/mat` or `/scorer`). If scorer has no mat, show message.
- **Admin:** New routes under manage (or `/admin/scorers`): list scorers, create (username, password, mat, active), edit (mat, active, password reset). Blade CRUD; auth: admin only.
- **Deliverables:** Migration(s), User update, scorer admin (controller + views), login redirect, auth check helpers. No new layout yet.

### Phase 2 – Mat Dashboard / Match List
- **Routes:** Scorer-only routes (middleware: auth + scorer, e.g. `mat_number` set). E.g. `GET /mat` or `GET /scorer` = mat dashboard.
- **Controller:** Load tournament (from user’s Tournament_id or first managed); load bouts for `bouts.mat_number = auth()->user()->mat_number`, tournament, optional `completed = 0`. Order by round, id.
- **View:** Dense table: bout #, division/weight, round, red wrestler, green wrestler, clubs, status. Link “Score” → mat-side scoring screen (Phase 4). Filters only if schema supports (e.g. round, completed).
- **Deliverables:** MatDashboardController (or ScorerController), Blade view, routes, middleware “scorer” (and optionally “scorer_mat_assigned”).

### Phase 3 – Scoring Data Layer ✅ Complete
- **Inspect:** Confirm no existing event/history tables (none found).
- **Migrations:** (1) `bout_scoring_state`: bout_id (or tournament_id + bout_id), red_wrestler_id, green_wrestler_id, red_score, green_score, period, clock_seconds, blood_red, blood_green, injury_red, injury_green, etc., status (pending/live/paused/completed), winner_id, result_type. (2) `bout_scoring_events`: bout_id, side (red/green/neutral), event_type (takedown, escape, etc.), points, period, match_time_snapshot, note, created_by (user_id).
- **Models:** BoutScoringState (belongsTo Bout, TournamentWrestler x2), BoutScoringEvent (belongsTo Bout, User).
- **Service:** e.g. `MatScoringService`: startBout(), recordEvent(), updateScore(), setPeriod(), setClock(), completeBout(). All writes to state + event table.
- **Deliverables:** Migrations, models, service class. No UI yet. See `docs/PHASE3_SCORING_DATA_LAYER_SUMMARY.md`.

### Phase 4 – Mat-Side Scoring Screen
- **Route:** e.g. `GET /mat/bout/{boutId}` (scorer only; authorize bout on scorer’s mat).
- **Controller:** Load bout, wrestlers, division; load or create BoutScoringState; load recent BoutScoringEvents.
- **View:** Blade, dense layout from reference images: red panel (name, club, score, timers, buttons), center (clock, period, bout info), green panel (same), comment/history area, add comment, horn placeholder, reset (with confirm). Buttons call JS → fetch endpoints (Phase 5).
- **Deliverables:** Controller, Blade view, CSS/JS for layout. Buttons/clock/timers can be placeholders or wired in Phase 5.

### Phase 5 – Scoring Functionality
- **Endpoints:** POST/GET for clock (get state, start/pause, set time), side timers (blood/injury/head-neck/recovery), record event (event type, side, points), add comment, set period, reset bout (confirm), complete bout (winner, result type). All persist state + append event.
- **Front-end:** JS (or Alpine/Livewire if already in project) for clock countdown, timer countdowns, button clicks → fetch. Lock completed bouts (read-only unless admin).
- **Deliverables:** API or form POST endpoints, JS, persistence and audit trail.

### Phase 6 – Summary / History / Settings
- **Summary/history:** Route `GET /mat/bout/{boutId}/history`; view events by period; link from scoring screen.
- **Settings:** Simple Blade page (e.g. display options, sound); placeholder or minimal.
- **Nav:** Scorer layout or section: Match list, (current bout), Summary, Settings, Logout.

---

## 15. Assumptions and Risks

- **matsideimages:** Not present at inspection. Plan assumes standard mat-side workflow; layout and terminology will be refined when images are available.
- **Scorer tournament:** Scorer may be tied to one tournament (`Tournament_id`) or given a way to choose tournament; Phase 1 can use `Tournament_id` for “which tournament I’m scoring” to avoid extra tables.
- **Username vs email:** Existing login is email-based. Phase 1 can add username login only for scorers (e.g. custom credential lookup) to avoid changing Fortify globally.
- **Existing scoring:** ManageScoreController stays as-is for admin/manager “result entry.” Mat-side is an additional path; finalizing a bout in mat-side can still update `bouts` (points, completed) for consistency with current reports.
- **Permissions:** No new package; keep accesslevel + policy/middleware (e.g. “scorer” middleware checks `isScorer()` and `mat_number` set).

---

## 16. Files to Create/Modify (High Level)

| Phase | New | Modify |
|-------|-----|--------|
| 1 | Migration (users: mat_number, maybe role); ScorerController (admin CRUD); Blade (admin: list/create/edit scorers); Login redirect (Fortify); middleware ScorerMatAssigned | User model; Fortify config or LoginResponse; routes (admin + scorer redirect) |
| 2 | MatDashboardController; Blade (match list); route(s); middleware | routes |
| 3 | Migrations (bout_scoring_state, bout_scoring_events); BoutScoringState, BoutScoringEvent; MatScoringService | — |
| 4 | MatScoringController; Blade (scoring screen); CSS/JS | routes |
| 5 | API or POST routes for clock, timers, events, comment, reset, complete; JS | MatScoringController; scoring view |
| 6 | History view + route; Settings view + route; scorer nav | Layout or scorer partial |

---

**Phase 0 is complete. No code has been written. Proceed to Phase 1 when approved.**
