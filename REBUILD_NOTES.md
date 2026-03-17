# AutoWrestle Rebuild Notes

This file tracks assumptions, ambiguous behavior, and TODOs during the rebuild from the legacy app (`orig-autowrestle`) and SQL dump (`autowrestle.sql`).

## Assumptions

- **Legacy codebase location:** `orig-autowrestle` (not `/auto`) is the reference.
- **Auth:** Laravel uses the existing `users` table; legacy columns (`accesslevel`, `active`, `username`, `mycode`, `Tournament_id`) were added. Admin is `accesslevel === '0'`.
- **Composite keys:** `brackets` (id, wr_Id, wr_pos) and `bouts` (id, Wrestler_Id) are left as in the dump; Eloquent models use `$incrementing = false` and lookups by `where()` where needed.
- **Division id:** Treated as globally unique (single PK `id`).
- **Column names:** Legacy PascalCase and mixed case (e.g. `Wrestler_Id`, `Tournament_Id`) preserved in DB and models.

## Ambiguous / To Verify

- **Scoring:** Meaning of `points` vs `score`, `color`, `wrtime` (match time?), `pin` (0/1) in `bouts` – preserve behavior; document when clarified.
- **Tournament status:** 0/1/2 – assumed 0=upcoming, 1=open, 2=locked; confirm in legacy.
- **unbouted table:** Usage in legacy code to be verified before changing.
- **workers, v, wrestelrsview:** Recreate only if needed by rebuild features.

## TODOs

- [ ] Team sheet / scan sheet prints (if needed).
- [ ] Legacy password hashes: some users have MySQL `*...` style; plan reset or migration.

## Completed

- REBUILD_PLAN.md created from audit.
- Migrations created from SQL dump (users legacy columns, password_resets, tournamenttypes, tournaments, clubs, wrestlers, tournamentusers, divisions, divgroups, boutsettings, tournamentwrestlers, brackets, bouts, teamrequest, unbouted).
- Eloquent models: User, Tournament, TournamentType, Club, Wrestler, Division, DivGroup, TournamentWrestler, Bracket, Bout, BoutSetting, TeamRequest; relationships defined.
- Registration: routes, controller, views (register, addwrestler, locked); add/withdraw wrestlers; locked state.
- Division and group CRUD: ManageDivisionController, ManageGroupController, routes, views.
- BracketGenerationService and ManageBracketController: create brackets, show by group, unbracket, delete wrestler.
- BoutGenerationService and ManageBoutController: create bouts, unbout, print bouts (HTML); BoutSettingsSeeder.
- Scoring: ManageScoreController index/show/update; enter bout #, enter points/time/win type, save.
- Check-in: ManageCheckinController index/show/update/print; list groups, toggle check-in, print by division.
- Wrestler add/edit: WrestlerController create/store/edit/update and views.
