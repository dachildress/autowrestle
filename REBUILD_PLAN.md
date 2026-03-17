# AutoWrestle Rebuild Plan

**Document version:** 1.0  
**Source:** Audit of legacy app (`orig-autowrestle`) and database dump (`autowrestle.sql`)

**Note:** The legacy codebase lives in `orig-autowrestle` (not `/auto`). All references below are to `orig-autowrestle` and `autowrestle.sql` in the project root.

---

## 1. Inferred Feature List

### 1.1 Public / Guest
- **Home** – Landing page, tournament list
- **Tournament list** – Current/upcoming tournaments (`/tournaments/list`)
- **Tournament show** – View tournament details and registered wrestlers
- **Tournament registration** – Register for a tournament; add/remove wrestlers; locked state when registration closed
- **My bouts** – Wrestler/parent view of their bouts for a tournament (search, view)
- **Wrestlers** – User’s wrestler list; add/edit wrestlers (auth required)
- **Team request** – Request a new club/team (newteam)
- **Auth** – Login, register, password reset (Laravel auth)
- **Mat view** – Mat-specific view (`/matview/{matid}`)
- **About / Contact** – Static pages

### 1.2 Tournament Management (Admin)
- **Manage dashboard** – Tournament management home (`/tournaments/manage`, `/tournaments/manage/{id}`)
- **Tournament edit** – Name, date, message, link, allow double, status, open date, view wrestlers, type
- **Check-in** – Weigh-in/check-in: list wrestlers, update check-in status, remove no-shows
- **Print check-in** – Print check-in sheet
- **Division management** – List, add, edit, view, delete divisions (per tournament)
- **Group management** – Add, edit, delete groups within a division (age/grade/weight rules, bracket type)

### 1.3 Bracket & Bout Logic
- **Bracket by division** – Create brackets for a division (groups → brackets using `boutsettings` / BracketType)
- **Show brackets** – View bracket groups and wrestler positions
- **Unbracket** – Clear brackets for a division
- **Move wrestler to new bracket** – Reassign wrestler to another bracket
- **Delete wrestler** (from bracket/tournament)
- **Bout groups** – Generate bouts from brackets for a division
- **Unbout** – Clear bouts for a division
- **Print brackets** – Print bracket view
- **Print bouts** – Print bout sheets by round
- **Scoring** – Enter/display match scores (points, pin, time, color, scored); index and show views

### 1.4 Reports / Printouts
- Bracket print (by division)
- Bout print (by division, round)
- Team sheet print
- Scan sheet print
- Check-in print

### 1.5 Admin / Auth Behavior
- **Users** – `users` table: name, last_name, phone, email, password, accesslevel, active, username, mycode, Tournament_id
- **Tournament users** – Links users to tournaments they can manage
- **Access** – `accesslevel` '0' = admin; '10' = normal (inferred from data). Auth middleware on manage routes.
- **newusers** – Separate table (Laravel default migrations); may be unused; legacy uses `users`.

### 1.6 Helpers / Legacy Rules
- **Helpers.php** – `flash()`, `getDivisionName($divisionId, $groupId)`, `getTournament($id)` (latter nested incorrectly in PHP)
- **BracketInfo class** – Builds bracket display data (bouts by round, wrestler info, points)
- **Info class** – Tournament/session context (used in manage controllers)
- **boutsettings** – Lookup table for bracket structure (BoutType 2,3,4,5,6 = size; Round, PosNumber, AddTo for bracket positions)
- **Madison-style** – Grouping by weight, age, experience; round-robin style (tournament type 1 = Round Robin)

---

## 2. Inferred Database Entities and Relationships

### 2.1 Core Tables (from autowrestle.sql)

| Table | Purpose | PK | Notes |
|-------|---------|-----|-------|
| **users** | Coaches/parents (auth) | id | accesslevel, active, username, mycode, Tournament_id |
| **wrestlers** | Master wrestler records | id | wr_*, user_id (owner), club name as string |
| **clubs** | Club lookup (id, Club name) | id | Wrestlers store club name; clubs used for dropdowns |
| **tournaments** | Tournament definition | id | Status, OpenDate, AllowDouble, ViewWrestlers, Type |
| **tournamenttypes** | Type lookup (e.g. Round Robin) | id | |
| **tournamentusers** | User ↔ Tournament (who can manage) | Id | Tournament_id, User_id |
| **tournamentwrestlers** | Wrestler in a tournament (snapshot + group) | id | Wrestler_Id, Tournament_id, group_id, weight, bracket pos, checked_in |
| **divisions** | Division within tournament (e.g. PW, JR) | id + composite | Tournament_Id, StartingMat, TotalMats, PerBracket, bouted, Bracketed |
| **divgroups** | Group within division (age/grade/weight rules) | id + composite | Tournament_Id, Division_id, BracketType, MaxWeightDiff, MaxExpDiff, bracketed, bouted |
| **brackets** | Wrestler placement in a bracket | (id, wr_Id, wr_pos) | id = bracket number; wr_Id = tournamentwrestlers.id; wr_pos = position |
| **bouts** | Match between two wrestlers | (id, Wrestler_Id) | Two rows per match; id = bout id; Wrestler_Id = tournamentwrestlers.id; Bracket_Id, Division_Id, round, points, pin, etc. |
| **boutsettings** | Bracket template (round/position for 2–6 person brackets) | id | BoutType, Round, PosNumber, AddTo |
| **brackettotal** | Aggregation/view helper | — | |
| **unbouted** | Temporary: brackets not yet given bouts | — | MatNumber, Bracket_Id, Tournament_id |
| **teamrequest** | New club request (Name, user_id, status) | id | |
| **migrations** | Laravel migrations | — | |
| **password_resets** | Laravel | — | |
| **sessions** | Laravel | — | |
| **newusers** | Laravel default users (likely unused) | id | |

### 2.2 Views (stand-in structures in dump)
- **bracketinfo** – Join of wrestler, bracket, division, group, bout data for display
- **v** – Unknown
- **workers** – Unknown (key on id)
- **wrestelrsview** / **wrestelrsview2** – Wrestler list views

### 2.3 Foreign Keys (from SQL)
- `bouts.Tournament_Id` → `tournaments.id`
- `bouts.Wrestler_Id` → `tournamentwrestlers.id`
- `brackets.Tournament_Id` → `tournaments.id`
- `brackets.wr_Id` → `tournamentwrestlers.id`
- `tournamentwrestlers.Tournament_id` → `tournaments.id`

**Missing FKs in dump:** divisions → tournaments; divgroups → divisions/tournaments; tournamentwrestlers.Wrestler_Id → wrestlers; wrestlers.user_id → users; tournamentusers → users/tournaments. These should be added in migrations where safe.

### 2.4 Naming Conventions (Legacy)
- PascalCase columns: `Wrestler_Id`, `Bracket_Id`, `Tournament_Id`, `Division_Id`, `DivisionName`, etc.
- Tables: lowercase (`tournamentwrestlers`, `divgroups`)
- Typo: `wrestelrsview` (preserve in DB if recreated; use readable names in app)

### 2.5 Enum-like / Flags (Document for validation)
- **bouts:** `pin` (0/1), `color` (0/1?), `scored` (0/1), `printed` (0/1)
- **brackets:** `bouted` (0/1), `printed` (0/1)
- **tournaments:** `status` (0=?, 1=open?, 2=locked?), `AllowDouble` ('0'/'1'), `ViewWrestlers` (0/1)
- **users:** `accesslevel` ('0'=admin, '10'=user), `active` ('0'/'1')
- **teamrequest:** `status` (0/1)
- **divgroups:** `BracketType` ('4','5','6' = bracket size; from boutsettings.BoutType)

---

## 3. Legacy Code vs Schema Comparison

### 3.1 What Matches
- Routes and controllers align with tables: Tournaments, TournamentWrestler, Division, DivGroup, Bracket, Bout, Wrestler, User, Club, TeamRequest.
- Bracket generation uses `boutsettings` (BracketType = 2,3,4,5,6) and `divgroups` (MaxWeightDiff, MaxExpDiff, etc.).
- Bouts store two rows per match (Wrestler_Id per competitor); BracketController/BoutController and BracketInfo assume this.
- Division → Groups → Brackets → Bouts flow is consistent in code and schema.

### 3.2 Gaps / Inconsistencies
- **Helpers.php:** `getDivisionName` uses `Division_Id` in join but schema column is `Division_id` (case); `findorfail($id)->first()` is wrong (should be `findOrFail($id)`).
- **newusers** – Table exists; legacy auth uses `users`. Prefer single `users` table; ignore or migrate newusers only if needed.
- **boutsettings** – Referenced by BracketType (2–6); no FK. Keep as lookup table.
- **unbouted** – Appears to be a working table for mat assignment; usage in legacy code should be verified.
- **workers** – No clear use in audited routes; may be legacy or optional.
- **divisions** – Composite PK in SQL; Eloquent typically expects single `id`; schema shows `id` as first part of PK. Confirm whether division `id` is unique across tournaments (data suggests it is).

### 3.3 Recreate vs Improve
- **Recreate (behavior preserved):** Bracket generation algorithm, bout creation from brackets, scoring fields (points, pin, wrtime, color, scored), check-in flow, registration flow, tournament/division/group CRUD.
- **Improve:** Use Laravel auth on `users` (with accesslevel/active); add proper FKs and indexes; Form Requests for validation; service classes for bracket/bout logic; consistent route names and REST where practical; Blade components/layouts; drop duplicate or unused tables (e.g. newusers) after confirmation.

---

## 4. Proposed Laravel Architecture

### 4.1 Domain Structure
- **Tournament** – Tournament CRUD; status; open/close registration.
- **Division** – Belongs to tournament; StartingMat, TotalMats, PerBracket; Bracketed/bouted flags.
- **DivGroup (Group)** – Belongs to division; age/grade/weight rules; BracketType; bracketed/bouted.
- **Wrestler** – Master record; belongs to user; club (string or club_id).
- **TournamentWrestler** – Pivot/snapshot for wrestler in tournament; group_id, weight, checked_in, bracket position.
- **Bracket** – Logical bracket: collection of (bracket id, tournament_wrestler id, position).
- **Bout** – Match: two rows per bout (Wrestler_Id = tournamentwrestlers.id); round, points, pin, etc.
- **Club** – Lookup for club name (wrestlers may store name; club_id optional later).
- **User** – Auth + profile; accesslevel for admin.
- **TournamentUser** – Pivot: which users manage which tournaments.

### 4.2 Suggested Directories
- `app/Models/` – Tournament, Division, DivGroup, Wrestler, TournamentWrestler, Bracket, Bout, Club, User, TournamentUser, BoutSetting, TournamentType, TeamRequest.
- `app/Http/Controllers/` – HomeController, TournamentController (public), RegistrationController, WrestlerController, Manage/TournamentController, Manage/DivisionController, Manage/GroupController, Manage/BracketController, Manage/BoutController, Manage/ScoreController, Manage/CheckInController.
- `app/Services/` – BracketGenerationService, BoutGenerationService (from boutsettings + brackets).
- `app/Http/Requests/` – StoreTournamentRequest, StoreWrestlerRequest, etc.
- `resources/views/` – layouts (app, manage), tournaments/, wrestlers/, manage/, auth/.

### 4.3 Route Conventions
- Public: `tournaments.list`, `tournaments.show`, `tournaments.register`, `wrestlers.*`, `mybouts.*`.
- Manage: `manage.tournaments.*`, `manage.divisions.*`, `manage.groups.*`, `manage.brackets.*`, `manage.bouts.*`, `manage.scoring.*`, `manage.checkin.*`.
- Auth: Laravel default (login, register, password.reset).

---

## 5. Migration Plan

1. **Base migrations from SQL**
   - Create tables in dependency order: users, clubs, tournamenttypes, tournaments, password_resets, sessions, migrations; wrestlers (user_id); tournamentusers; tournamentwrestlers; divisions; divgroups; boutsettings; brackets; bouts; teamrequest; unbouted.
   - Preserve legacy column names initially (Wrestler_Id, Bracket_Id, etc.) to avoid breaking data; map in Eloquent with `$fillable` and relations.
   - Add FKs: tournamentwrestlers → wrestlers, divisions → tournaments, divgroups → divisions + tournaments, bouts → Division_Id if desired (currently denormalized).
   - Add indexes for tournament_id, division_id, bracket_id, Wrestler_Id where missing.
2. **Views**
   - Recreate `bracketinfo` as a DB view or Eloquent query/accessor; skip or defer `v`, `workers`, `wrestelrsview` until needed.
3. **Seeders**
   - Optional: boutsettings (reference data), tournamenttypes; one admin user for dev.
4. **Rename / normalize later**
   - After rebuild is stable, consider migration to snake_case columns and single `id` PKs where composite is not strictly required; document every rename.

---

## 6. Proposed Rebuild Order

1. **Phase 2a – Foundation**
   - Migrations from SQL (all core tables).
   - Eloquent models and relationships (including composite keys / custom keys where needed).
   - Auth: use `users` table; middleware for admin (accesslevel) and tournament-manage (tournamentusers).
2. **Phase 2b – Core CRUD**
   - Tournaments (public list/show; manage edit).
   - Divisions and Groups (manage).
   - Wrestlers (user’s wrestlers) and Clubs (dropdown).
   - Tournament registration (add/remove wrestler; locked when tournament closed).
3. **Phase 3a – Bracket & Bouts**
   - BracketGenerationService (from legacy BracketController + boutsettings).
   - BracketController: create brackets per division, show brackets, unbracket, move wrestler, delete wrestler.
   - BoutGenerationService and BoutController: create bouts, unbout, print.
   - BracketInfo-style logic for display (service or view model).
4. **Phase 3b – Scoring & Check-in**
   - ScoreController: list bouts, enter/update scores (points, pin, time, color).
   - Check-in: update checked_in, remove no-shows; print check-in.
5. **Phase 3c – Public & Reports**
   - My bouts (search/view).
   - Printouts: brackets, bouts, team sheet, check-in.
   - Mat view (if required).
6. **Phase 4**
   - REBUILD_NOTES.md: assumptions, ambiguous fields, TODOs.
   - Manual testing against legacy behavior; fix discrepancies.

---

## 7. Major Risks and Unclear Areas

1. **Bracket generation algorithm**
   - Logic lives in BracketController (`bracketgroup()` and use of boutsettings). Must be reverse-engineered and reimplemented in a service without changing behavior (grouping by weight/experience, position assignment). High risk if misread.

2. **Bout creation from brackets**
   - How bracket positions map to bout pairs (and rounds) depends on boutsettings (BoutType, Round, PosNumber, AddTo). Must match legacy exactly for correct round-robin/placement.

3. **Composite primary keys**
   - `brackets` (id, wr_Id, wr_pos), `bouts` (id, Wrestler_Id), `divgroups` (id, Tournament_Id, Division_id), `divisions` (composite in SQL). Eloquent support for composite keys may require custom key setup or raw queries in places.

4. **Division id uniqueness**
   - Division `id` appears unique globally in data; if so, single `id` PK is fine. Confirm no same `id` across tournaments.

5. **Scoring semantics**
   - Meaning of `points` vs `score`, `color`, `wrtime` (match time?), `pin` (0/1). Preserve until clarified; document in REBUILD_NOTES.

6. **Tournament status**
   - Exact meaning of status (0/1/2) and when registration locks. Inferred: 0=upcoming, 1=open, 2=locked; verify in legacy.

7. **accesslevel and mycode**
   - accesslevel '0' vs '10'; mycode may be for parent/child linking or legacy login; document and keep column until auth is fully migrated.

8. **Legacy password hashes**
   - Some users have MySQL `*...` style hashes; Laravel uses bcrypt. Plan for migration or “reset password” for those users.

9. **unbouted / workers**
   - Confirm usage before dropping or changing.

10. **Views v, workers, wrestelrsview(s)**
    - Recreate only if referenced in rebuild; otherwise defer.

---

## 8. Summary for Stakeholder

- **Inferred features:** Public tournament list/registration, my bouts, wrestler CRUD; admin tournament/division/group/bracket/bout management, check-in, scoring, and printouts.
- **Inferred entities:** Users, Wrestlers, Clubs, Tournaments, Divisions, Groups, TournamentWrestlers, Brackets, Bouts, BoutSettings, TournamentTypes, TournamentUsers, TeamRequest.
- **Proposed rebuild order:** Migrations + models + auth → Tournament/Division/Group/Wrestler/Registration CRUD → Bracket service + Bracket/Bout controllers → Scoring + Check-in → Public/reports → REBUILD_NOTES and testing.
- **Risks:** Bracket and bout generation logic must be replicated exactly; composite keys and legacy column names require care; scoring and tournament status semantics should be documented and preserved until clarified.

Next step: proceed with Phase 2 (migrations, models, auth, route scaffold) in `autowrestle`.
