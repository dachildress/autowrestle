# AutoWrestle Design System

## Scope

- **In scope:** Public pages, admin (manage.*), registration, dashboard, reports, auth (login/register/forgot).
- **Out of scope:** All mat-side scoring pages and any scoring-related UI (bout-show, bout-results, bout-history, mat dashboard, mat settings, virtual display/settings). These keep their current layout and styling.

## Visual Identity

- **Base:** Dark navy / charcoal for headers and key surfaces.
- **Accent:** Strong accent (e.g. red `#c00`, gold `#d4a012`, or electric blue `#2563eb`) for CTAs, highlights, and links.
- **Backgrounds:** Light gray for body (`#f1f5f9`), white or very light for cards.
- **Typography:** Clean, readable sans-serif (e.g. system-ui or Inter); bold section headers; clear hierarchy.

## Design Tokens (Tailwind)

- **Primary (navy):** `#0f172a` (slate-900)
- **Secondary (charcoal):** `#1e293b` (slate-800)
- **Accent:** Configurable; default `#dc2626` (red-600) or `#2563eb` (blue-600)
- **Success:** `#16a34a` (green-600)
- **Danger:** `#dc2626` (red-600)
- **Border:** `#e2e8f0` (slate-200)
- **Text:** `#0f172a` (primary), `#64748b` (muted)

## Components

| Component   | Use |
|------------|-----|
| **Button** | Primary, secondary, danger, ghost; consistent padding and radius. |
| **Card**   | Container with border, shadow, optional header. |
| **Stat block** | Number + label for dashboard/summary. |
| **Table**  | Striped or bordered; th with primary background. |
| **Badge**  | Status, counts; small and pill-shaped. |
| **Alert**  | Success, error, warning, info. |
| **Form**   | Label above or left; consistent input height and radius. |
| **Page header** | Title + optional breadcrumbs + actions. |
| **Nav**    | Horizontal bar; dropdowns for manage. |

## Layout

- **Container:** max-w-7xl mx-auto px-4 (or similar).
- **Page header:** mb-6; breadcrumbs when useful.
- **Cards:** Prefer card-based sections over raw full-width tables where it fits.
- **Spacing:** Consistent gap-4 / gap-6 and padding p-4 / p-6.

## Mat Isolation

- Mat pages extend `layouts.mat` (dedicated layout).
- `layouts.autowrestle` is used only for public and manage; it receives the new design and Tailwind.
- No shared component or style change affects mat scoring UI.

## Site Content (editable from admin)

- **Config:** `config/site_content.php` defines sections (key, label, type, default).
- **Storage:** `site_content` table stores overrides; `content('key')` and `site_content_image('key')` in Blade.
- **Admin:** Manage → user menu → “Site content” (admin only). Edit text or upload/replace images.
- **Images:** Stored under `storage/app/public/site-content/`. Run `php artisan storage:link` if not already.
