# Feature Specification: Phase 2 — UI Foundation: Static Pages → Blade

**Feature Branch**: `002-ui-foundation-blade`

**Created**: 2026-09-20

**Status**: Draft

**Input**: User description: "Read PLAN.md and create specification for Phase 2 — UI Foundation: Static Pages → Blade"

## Clarifications

### Session 2026-09-20

- Q: How should the existing static CSS and JavaScript files from pre-views/ be bundled within the Vite asset pipeline? → A: Option B — Modular Page Bundles: Configure `vite.config.js` with separate entrypoints per phase (e.g., `resources/css/pages/*.css`, `resources/js/pages/*.js`) included conditionally per Blade view.
- Q: How should Arabic font resources (e.g., Cairo / Tajawal) be loaded across all Blade views in layouts/app.blade.php? → A: Option A — Self-Hosted Web Fonts: Place font files in `resources/fonts/` and bundle via CSS `@font-face` rules in `resources/css/app.css` for full offline capability.
- Q: What content depth should be included when creating the skeleton Blade views in Phase 2 for the static HTML pages in pre-views/? → A: Option C — Componentized Layout Scaffolding: Copy full static HTML structures from `pre-views/` into target Blade views while extracting repeated UI elements (cards, data tables, search filters, form groups, status badges) into reusable Blade components in `resources/views/components/`.
- Q: How should global data and helper utilities in pre-views/shared-data.js be structured within Vite ES modules? → A: Option A — ES Modules with Window Binding: Convert `shared-data.js` to an ES module in `resources/js/shared-data.js` with explicit exports and bind `window.WCCMS` for backward compatibility with inline script handlers across Blade views.
- Q: How should static Arabic user interface strings in master layout partials (header, sidebar, footer) be stored and rendered? → A: Option A — Laravel Language Files: Abstract layout strings into `lang/ar/` files (e.g., `lang/ar/navigation.php`, `lang/ar/ui.php`) rendered via Laravel `__('navigation.item')` helpers.
- Q: How should the Data Field ↔ Schema mapping document (docs/ui-schema-mapping.md) be structured and formatted across the 8 static HTML pages? → A: Option A — Per-Page Mapping Tables with Gap Summary: Structure `docs/ui-schema-mapping.md` with dedicated mapping tables per static template (`[UI Field/Label]` → `[Table.Column]` → `[Enum Constraint Validation]`) followed by a consolidated section listing all unmatched schema gaps.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Vite Asset Pipeline & Static Asset Migration (Priority: P1)

As a frontend developer, I need existing hand-built CSS and JS assets from `pre-views/` configured under Laravel's Vite asset pipeline so that static styles, scripts, and shared data modules compile cleanly into modern application bundles without losing existing styling or interactive behavior.

**Why this priority**: Establishing Vite asset compilation is the prerequisite for converting static HTML files into functioning Blade views.

**Independent Test**: Can be tested independently by running `npm run build` and verifying that `public/build/manifest.json` produces bundled assets for `resources/css/app.css` and `resources/js/app.js` without compilation errors.

**Acceptance Scenarios**:

1. **Given** existing static CSS (`custom.css`, `requests.css`, `daily-review.css`, etc.) and JS files (`shared-data.js`, `requests.js`, etc.) in `pre-views/`, **When** Vite asset bundling is executed, **Then** assets compile cleanly without missing reference errors.
2. **Given** `@vite(['resources/css/app.css', 'resources/js/app.js'])` directives in Blade views, **When** loaded in browser, **Then** styles and script handlers load correctly.

---

### User Story 2 - Master Blade Layout & First-Class Arabic/RTL Support (Priority: P2)

As a clinical user (nurse/supervisor/admin), I need a unified, master Blade layout (`layouts/app.blade.php`) that natively supports Arabic language, Right-to-Left (RTL) layout direction, shared header/navigation, sidebar, and footer sections so that UI structure is consistent across all application views.

**Why this priority**: Centralizing repeated UI structures and RTL layout rules in a single master layout avoids duplicating HTML wrappers per page.

**Independent Test**: Can be tested independently by rendering a dummy Blade view extending `layouts/app.blade.php` and verifying that the page renders with `dir="rtl"`, `lang="ar"`, proper Arabic typography, and shared sidebar/header navigation.

**Acceptance Scenarios**:

1. **Given** any Blade view extending `layouts/app.blade.php`, **When** rendered in the browser, **Then** the page automatically includes `dir="rtl"` and `lang="ar"` attributes with proper Arabic font stack rendering.
2. **Given** shared UI partials (`header.blade.php`, `sidebar.blade.php`, `footer.blade.php`), **When** rendered within the master layout, **Then** navigation links and user indicators display consistently.

---

### User Story 3 - Static Page Inventory & Phase Ownership Mapping (Priority: P3)

As a developer, I need an inventory mapping all 8 static pages in `pre-views/` (`index.html`, `admin.html`, `requests.html`, `registered-patients.html`, `daily-review.html`, `scheduled-reviews.html`, `patient-history.html`, `complete-registration.html`) to their owning feature phases (Phase 3 through Phase 9), creating skeleton Blade routes and layout scaffolding for each.

**Why this priority**: Mapping existing static templates ensures no design asset is lost and each feature phase has a clear starting Blade view.

**Independent Test**: Can be tested independently by auditing all 8 static templates in `pre-views/` against the phase ownership matrix and verifying skeleton Blade views exist under `resources/views/`.

**Acceptance Scenarios**:

1. **Given** the 8 static HTML pages in `pre-views/`, **When** inventoried, **Then** every page is assigned to a specific downstream feature phase (e.g. `index.html` → Phase 3 Auth, `requests.html` → Phase 4 Requests).
2. **Given** skeleton Blade views for each static page, **When** accessed via route placeholders, **Then** each view extends `layouts/app.blade.php` and renders its layout structure.

---

### User Story 4 - Data Field ↔ Schema Consistency Audit (Priority: P2)

As a developer, I need every data field/label displayed across the 8 static pages in `pre-views/` cross-referenced against the Phase 1 database schema (table, column, and enum values) before any page is converted into a data-bound Blade view, so that mismatches between the UI and the real data model are caught and resolved while the fix is still cheap, rather than discovered mid-implementation in a later phase.

**Why this priority**: Converting pages to Blade and building controllers/routes against a UI whose fields were never validated against the actual schema risks silently baking in a data model that doesn't exist, or drifting from the naming/enum values locked in Phase 1.

**Independent Test**: Can be tested independently by producing `docs/ui-schema-mapping.md` and verifying every field listed for each static page resolves to either (a) an existing `table.column` from the Phase 1 schema, or (b) an explicitly flagged gap with a proposed resolution.

**Acceptance Scenarios**:

1. **Given** the 8 static pages inventoried in User Story 3, **When** each page's displayed fields/labels are audited against the Phase 1 schema, **Then** a mapping document records, for every field, its source `table.column` (or `NO MATCH` if none exists).
2. **Given** a field flagged as `NO MATCH` (e.g. a "Reviewed By" label on `requests.html` with no `reviewed_by` column in `WC_REQUEST_REG_TB`), **When** the audit is complete, **Then** the gap is listed as an open item referencing the corresponding Phase 1 open decision, not silently implemented or dropped.
3. **Given** a field whose displayed values are an enum (e.g. request status badges), **When** audited, **Then** the mapping confirms the displayed label set matches the exact enum values defined in the Phase 1 CHECK constraint (e.g. `PENDING | APPROVED | REJECTED | CANCELLED`), flagging any mismatch (extra, missing, or misspelled states).

---

### Edge Cases

- **CSS Selectors Collision**: Merging multiple static CSS files into Vite pipeline must avoid class naming collisions or unexpected cascade overrides.
- **RTL Alignment Inversion**: Flexbox and grid directions in custom CSS must maintain proper right-to-left visual flow for Arabic text.
- **Static Script Global Dependencies**: Scripts relying on `shared-data.js` globals MUST be refactored into ES modules (`resources/js/shared-data.js`) while binding `window.WCCMS` for legacy static script compatibility.
- **Field With No Schema Match**: A static page may display a field (e.g. "Reviewed By", "Visit Serial") that has no corresponding column in the Phase 1 schema. This MUST be documented as an open gap in `docs/ui-schema-mapping.md`, not implemented ad hoc or silently dropped during Blade conversion.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST configure `vite.config.js` with modular page-specific asset entrypoints for CSS and JS assets (e.g., `resources/css/app.css`, `resources/css/pages/*.css`, `resources/js/app.js`, `resources/js/pages/*.js`) so that views can conditionally include page-specific stylesheets and scripts alongside master layout assets.
- **FR-002**: System MUST create a central master Blade layout `resources/views/layouts/app.blade.php` utilizing `@yield('title')` and `@yield('content')`.
- **FR-003**: Master layout MUST enforce Right-to-Left directional rendering (`dir="rtl"`) and Arabic language context (`lang="ar"`) as a first-class requirement, bundling self-hosted Arabic web fonts (e.g., Cairo) from `resources/fonts/` via `@font-face` rules.
- **FR-004**: System MUST extract repeating layout sections into modular Blade partials (`resources/views/partials/header.blade.php`, `resources/views/partials/sidebar.blade.php`, `resources/views/partials/footer.blade.php`) and abstract all static Arabic UI text into Laravel language files (`lang/ar/navigation.php`, `lang/ar/ui.php`) rendered using `__('key')` helpers.
- **FR-005**: System MUST document and implement a complete template inventory mapping each `pre-views/` static page to its owning phase, creating Blade layout scaffolding for each target view with repeated UI elements (cards, data tables, search filters, status badges) extracted into reusable Blade components in `resources/views/components/`:
  - `pre-views/index.html` → Phase 3 (Auth & Shell)
  - `pre-views/requests.html` → Phase 4 (Registration Requests)
  - `pre-views/registered-patients.html` & `complete-registration.html` → Phase 5 (Registration & Diagnosis)
  - `pre-views/daily-review.html` → Phase 6 (Daily Review)
  - `pre-views/scheduled-reviews.html` → Phase 7 (Scheduled Reviews)
  - `pre-views/admin.html` → Phase 8 (Master Data Admin)
  - `pre-views/patient-history.html` → Phase 9 (Reporting & Dashboards)
- **FR-006**: System MUST replace hardcoded `<link>` stylesheet and `<script>` tags with Laravel `@vite(['resources/css/app.css', 'resources/js/app.js'])` directives.
- **FR-007**: Before any static page listed in the Template Ownership Matrix is converted into a data-bound Blade view (within its owning phase), the system MUST produce and maintain `docs/ui-schema-mapping.md` using per-page mapping tables (`[UI Field/Label]` → `[Table.Column]` → `[Enum Constraint Validation]`) and a consolidated Gap Summary section, documenting for every data field/label on that page: (a) its resolved `table.column` from the Phase 1 schema, (b) for enum-backed fields, confirmation that displayed values match the schema's CHECK constraint values exactly, and (c) an explicit gap entry — cross-referenced to the relevant Phase 1 open decision where applicable — for any field with no schema match. This requirement covers documentation and gap identification only; resolving a genuine schema gap is an amendment to the Phase 1 specification, not work performed within Phase 2.

### Template Ownership Matrix

| Static Page (`pre-views/`) | Blade Target (`resources/views/`) | Owning Phase | Purpose |
|----------------------------|----------------------------------|--------------|---------|
| `index.html` | `auth/login.blade.php` & `dashboard.blade.php` | Phase 3 | Authentication & Main Shell |
| `requests.html` | `requests/index.blade.php` | Phase 4 | Registration Requests Queue |
| `registered-patients.html` | `registrations/index.blade.php` | Phase 5 | Active Patient Registrations |
| `complete-registration.html` | `registrations/create.blade.php` | Phase 5 | Registration & Diagnosis Entry |
| `daily-review.html` | `reviews/daily.blade.php` | Phase 6 | Daily Review Visit Assessment |
| `scheduled-reviews.html` | `reviews/scheduled.blade.php` | Phase 7 | Appointment Calendar & Invalidation |
| `admin.html` | `admin/index.blade.php` | Phase 8 | Master Reference Data Management |
| `patient-history.html` | `reports/history.blade.php` | Phase 9 | Clinical History & Dashboards |

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: `npm run build` compiles Vite assets into `public/build/` with 0 errors.
- **SC-002**: 100% of rendered layout pages display native RTL orientation (`dir="rtl"`) and Arabic typography.
- **SC-003**: 8 out of 8 static HTML pages from `pre-views/` are mapped to skeleton Blade views without orphaned design files.
- **SC-004**: Shared layout partials (header, sidebar, footer) are instantiated exactly once in `layouts/app.blade.php`.
- **SC-005**: `docs/ui-schema-mapping.md` contains a mapping entry for 100% of data fields/labels across all 8 static pages, with zero fields left unresolved — each is either matched to a schema `table.column` or explicitly logged as a gap.

---

## Assumptions

- **Asset Preservation**: Existing CSS and JS assets in `pre-views/` are visually complete and serve as the design source of truth.
- **Vite Integration**: Laravel 11's default Vite setup with `laravel-vite-plugin` will bundle the CSS/JS files into standard production assets.
- **RTL Baseline**: Font stack for Arabic rendering relies on standard system fonts (Cairo, Tahoma, Arial) or Google Arabic fonts loaded in the layout head.
- **Audit Scope**: The consistency audit in User Story 4 documents mismatches only; resolving a genuine schema gap (e.g. adding a `reviewed_by` column) is handled as an amendment to the Phase 1 specification, not implemented directly within Phase 2.