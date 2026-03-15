# Reporting & Placements – Schema and Logic Audit

## Current schema (relevant to reporting)

### Brackets
- **Table:** `brackets`  
- **Primary key:** composite `(id, wr_Id, wr_pos)`  
- **Meaning:** One row per wrestler slot in a bracket. `id` = bracket identifier (unique per bracket). `wr_Id` = tournament wrestler id. `wr_pos` = position (0-based).  
- **Links:** `Tournament_Id`, `Division_Id`. No direct `group_id`; group comes from `tournamentwrestlers.group_id` for wrestlers in the bracket.

### Bouts
- **Table:** `bouts`  
- **Primary key:** composite `(id, Wrestler_Id)` – two rows per match (one per wrestler).  
- **Meaning:** `id` = bout (match) id. `Bracket_Id` links to bracket. `round` = round number (1–5). `completed` = true when the match has been finished (set by `MatScoringService::completeBout()`).

### Bout scoring state
- **Table:** `bout_scoring_state`  
- **Meaning:** One row per bout. `status` = 'completed' when match is finished. `winner_id` = tournament wrestler id of winner (null for double-forfeit).  
- **Synced with bouts:** When a bout is completed via mat scoring, `bouts.completed` is set to true and `bout_scoring_state.status` = 'completed', `winner_id` set.

### Tournament wrestler
- **Table:** `tournamentwrestlers`  
- **Relevant:** `wr_bracket_id`, `wr_bracket_position`, `group_id`, `wr_first_name`, `wr_last_name`, `wr_club`, `Tournament_id`, `Wrestler_Id` (global wrestler).

### Divisions & groups
- **divisions:** `Tournament_Id`, `DivisionName`, etc.  
- **divgroups:** Composite key `(id, Tournament_Id, Division_id)`. `Name`, `BracketType`, etc. Wrestlers in a group have `group_id` = divgroups.id (within same tournament).

## Bracket completion (current behavior)

- **No dedicated “bracket completed” flag** in the DB.  
- Completion is inferred: **all bouts that belong to the bracket have `bouts.completed` = true** (equivalently, each such bout has a `bout_scoring_state` row with `status` = 'completed').  
- Safe rule: for a given `(Tournament_Id, Bracket_Id)`, count distinct bout `id`s from `bouts`; count distinct bout `id`s in `bout_scoring_state` with `status` = 'completed' and `tournament_id`/`bout_id` matching. If they are equal and all have a resolved result (winner or explicit double-forfeit), bracket is complete.

## Placements (current behavior)

- **No stored placement table.** Placements are not written to the DB today.  
- **Bracket types:** Round-robin (2–6 wrestlers). Bout generation uses `boutsettings`: BoutType 2–6, Rounds 1–5, positions.  
- **Placement rule:** From match results only. For each bracket, use **wins** from `bout_scoring_state.winner_id`: count how many completed bouts each wrestler (tournament wrestler id) won. Sort by wins descending. Assign place 1, 2, 3, … (ties get same place; next place number skips accordingly, e.g. two 2nd → then 4th).  
- **Double-forfeit:** If `winner_id` is null, neither wrestler gets a win; both effectively have a loss for that bout. Placement still by total wins.

## What reporting adds

- A **reusable service** that:  
  - Determines if a bracket is completed (all bouts completed).  
  - Computes final placements from `bout_scoring_state` (wins per wrestler, sorted, then place 1..n with tie handling).  
- **No change** to scoring workflow or to how bouts/brackets are created or completed.  
- Reporting is read-only and uses existing data.
