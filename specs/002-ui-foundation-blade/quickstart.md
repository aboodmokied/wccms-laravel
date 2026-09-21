# Quickstart & Validation Guide: Phase 2 — UI Foundation: Static Pages → Blade

**Feature Branch**: `002-ui-foundation-blade`
**Date**: 2026-09-20
**Spec**: [spec.md](./spec.md) | **Data Model**: [data-model.md](./data-model.md)

---

## Prerequisites

1. **Environment**: PHP 8.3 with WAMP server running.
2. **Node.js & NPM**: Node 18+ installed.
3. **Dependencies**: `composer install` and `npm install` executed in repo root.

---

## Validation Steps

### Step 1: Execute Vite Build Pipeline

Verify that all CSS, JS, and self-hosted font assets compile cleanly into `public/build/`:

```powershell
npm run build
```

**Expected Outcome**:
- `npm run build` exits with code 0.
- Manifest file generated at `public/build/manifest.json`.
- Entrypoints for `resources/css/app.css`, `resources/js/app.js`, and page-specific bundles compiled without missing file errors.

---

### Step 2: Validate Master Blade Layout & RTL Rendering

Serve the Laravel development application:

```powershell
php artisan serve
```

Navigate to any skeleton route (e.g. `http://localhost:8000/dashboard` or `http://localhost:8000/requests`).

**Expected Outcome**:
- Page source contains `<html lang="ar" dir="rtl">`.
- Header, sidebar, and footer partials render consistently across all 8 mapped routes.
- Navigation titles render in Arabic using `lang/ar/` localization strings.
- Typography renders using self-hosted Cairo web fonts from `@font-face` rules.

---

### Step 3: Audit Template Inventory & Route Scaffolding

Verify that all 8 static pages from `pre-views/` load as Blade templates:

| Route Path | Expected Blade View | Rendered Verification |
|------------|---------------------|-----------------------|
| `/login` | `auth/login.blade.php` | Authentication layout rendered |
| `/dashboard` | `dashboard.blade.php` | Main shell dashboard layout rendered |
| `/requests` | `requests/index.blade.php` | Registration requests queue layout rendered |
| `/registrations` | `registrations/index.blade.php` | Registered patients layout rendered |
| `/registrations/create` | `registrations/create.blade.php` | Complete registration form layout rendered |
| `/reviews/daily` | `reviews/daily.blade.php` | Daily review visit assessment layout rendered |
| `/reviews/scheduled` | `reviews/scheduled.blade.php` | Scheduled reviews calendar layout rendered |
| `/admin` | `admin/index.blade.php` | Master reference data admin layout rendered |
| `/reports/history` | `reports/history.blade.php` | Patient clinical history report layout rendered |

---

### Step 4: Verify Blade Components Instantiation

Inspect page rendering for `<x-card>`, `<x-data-table>`, `<x-status-badge>`, `<x-search-filter>`, and `<x-form-group>`.

**Expected Outcome**:
- Repeated UI elements render cleanly without duplicate inline HTML wrappers.
- UI status pills (Active / Pending / Discharged) inherit styling from `status-badge.blade.php`.

---

### Step 5: Verify ES Module Window Binding

Open browser developer console (`F12`) on any rendered page:

```javascript
console.log(window.WCCMS);
```

**Expected Outcome**:
- `window.WCCMS` object is defined and exposes shared data utilities from `resources/js/shared-data.js`.

---

### Step 6: Validate Data Field ↔ Schema Audit (`docs/ui-schema-mapping.md`)

Inspect `docs/ui-schema-mapping.md` for 100% template field coverage across all 8 static pages:

**Expected Outcome**:
- Mappings documented for 8/8 static HTML templates.
- 100% of UI labels/fields mapped to `table.column` or explicitly listed as an unmatched gap.
- All enum-backed UI fields (e.g. request status, review result) confirmed against Phase 1 CHECK constraint values.
- Zero unresolved or unlogged fields remain.

