# Phase 1: Simple Scorer User Support – Summary

## What was changed

### Migrations
- **`2025_03_14_100000_add_mat_number_to_users_table.php`** – Adds nullable `mat_number` (unsigned tiny integer) to `users`.

### Model
- **`App\Models\User`**
  - Added `mat_number` to `$fillable` and `$casts` (integer).
  - Added `isScorer()`: returns `true` when `accesslevel === '5'`.

### Controllers
- **`App\Http\Controllers\Manage\ManageScorerController`** (new) – Admin-only CRUD for scorer users: index, create, store, edit, update, destroy. Uses `accesslevel = '5'` for scorers.
- **`App\Http\Controllers\MatDashboardController`** (new) – Shows mat dashboard for scorers; displays “no mat assigned” when `mat_number` is null.

### Responses
- **`App\Http\Responses\LoginResponse`** (new) – Implements Fortify `LoginResponse` contract; redirects scorers to `mat.dashboard`, others to intended/home.

### Routes
- **`routes/autowrestle_manage.php`** (under `auth`, prefix `tournaments/manage`):
  - `GET scorers` → `manage.scorers.index`
  - `GET scorers/create` → `manage.scorers.create`
  - `POST scorers` → `manage.scorers.store`
  - `GET scorers/{id}/edit` → `manage.scorers.edit`
  - `PATCH scorers/{id}` → `manage.scorers.update`
  - `DELETE scorers/{id}` → `manage.scorers.destroy`
- **`routes/autowrestle.php`** (inside `auth` group):
  - `GET /mat` → `mat.dashboard`

### Views
- **`resources/views/manage/scorers/index.blade.php`** – List of scorers (username, email, mat, active, Edit/Remove).
- **`resources/views/manage/scorers/create.blade.php`** – Form: username, email, password, confirm, mat number, active.
- **`resources/views/manage/scorers/edit.blade.php`** – Same fields; password optional (leave blank to keep).
- **`resources/views/mat/dashboard.blade.php`** – Placeholder mat dashboard; message when no mat assigned.
- **`resources/views/auth/login.blade.php`** – Label changed to “Email or username”; input type `text`.
- **`resources/views/manage/tournaments/index.blade.php`** – “Scorer users” link added for admin.

### Auth / Login
- **`App\Providers\FortifyServiceProvider`**
  - Registers custom `LoginResponse` in `register()`.
  - In `boot()`, adds `configureAuthentication()` which sets `Fortify::authenticateUsing()` to:
    - Resolve user by **email** (if input contains `@`) or **username**.
    - Validate password with `Hash::check`.
    - Reject inactive scorers (`active !== '1'`).

---

## How admin creates a scorer

1. Log in as an admin (accesslevel `'0'`).
2. Go to **Manage** (tournaments list).
3. Click **Scorer users**.
4. Click **Add scorer user**.
5. Enter **Username** (required, unique), **Email** (required, unique), **Password** (min 8, confirmed), **Mat number** (optional), check **Active**.
6. Submit. The user is created with `accesslevel = '5'`.

To edit: **Scorer users** → **Edit** on a row. Change username, email, mat, active; optionally set a new password (or leave blank to keep).  
To disable: Edit and uncheck **Active**; the scorer can no longer log in.  
To remove: **Remove** (with confirm); the user record is deleted.

---

## How scorer login works

1. Scorer goes to the login page.
2. Enters **Email or username** (the field accepts either) and **Password**.
3. Backend:
   - If input contains `@`, user is looked up by `email`; otherwise by `username`.
   - Password is checked with Laravel hashing.
   - If the user is a scorer and `active !== '1'`, login is rejected.
4. On success, **scorers** are redirected to **Mat dashboard** (`/mat`). Other users go to the intended URL or home.
5. On the mat dashboard:
   - If **no mat assigned** (`mat_number` null): “You have no mat assigned. Contact an administrator.”
   - If **mat assigned**: “You are assigned to Mat X.” and a short note that the match list comes in the next phase.

---

## How to test Phase 1

1. **Run migration**
   ```bash
   php artisan migrate
   ```

2. **Create a scorer (as admin)**
   - Log in as admin.
   - Visit `/tournaments/manage` → **Scorer users** → **Add scorer user**.
   - Create e.g. username `mat1`, email `mat1@test.com`, password `password`, mat `1`, Active checked.
   - Save.

3. **Scorer login with username**
   - Log out (or use another browser/incognito).
   - Log in with username `mat1` and password.
   - You should be redirected to `/mat` and see “You are assigned to Mat 1.”

4. **Scorer login with email**
   - Log in with `mat1@test.com` and same password.
   - Same redirect to `/mat`.

5. **No mat assigned**
   - Edit the scorer, clear Mat number, save.
   - Log in as that scorer → mat dashboard should show “You have no mat assigned.”

6. **Disabled scorer**
   - Edit the scorer, uncheck Active, save.
   - Try to log in as that scorer → login should fail.

7. **Non-scorer**
   - Log in as a normal user (e.g. manager) with email/password.
   - Should go to home or intended URL, not `/mat`.

8. **Direct mat dashboard**
   - As scorer: `/mat` shows dashboard.
   - As non-scorer: visiting `/mat` returns 403.

---

## Assumptions

- **Scorer role** is `accesslevel = '5'`. No new roles table; existing `accesslevel` is used.
- **Username** is required and unique for scorer creation/update. Existing users may have empty `username`; they are unchanged.
- **Email** remains required for scorers (used for display and possible password reset). Login can be done with username or email.
- **Inactive** scorers (`active = '0'`) cannot log in.
- **Mat dashboard** is a placeholder until Phase 2 (match list). No tournament context is chosen yet; that can be added when needed (e.g. `Tournament_id` or session).

Phase 1 is complete. Ready to proceed to Phase 2 (Mat dashboard / match list) when you are.
