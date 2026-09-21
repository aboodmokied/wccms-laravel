# Research & Design Decisions: Phase 2 — UI Foundation: Static Pages → Blade

**Feature Branch**: `002-ui-foundation-blade`
**Date**: 2026-09-20
**Spec**: [spec.md](./spec.md)

---

## Research Tasks & Decisions

### 1. Vite Multi-Entrypoint Configuration in Laravel 11 (`vite.config.js`)

- **Decision**: Configure `vite.config.js` to process the master asset bundles (`resources/css/app.css`, `resources/js/app.js`) alongside modular page-specific asset inputs (`resources/css/pages/*.css`, `resources/js/pages/*.js`).
- **Rationale**: Per clarification Q1, modular page bundles prevent CSS selector collisions across distinct feature screens (e.g. daily reviews vs master data admin) while avoiding bloated single-bundle downloads.
- **Alternatives Considered**: 
  - *Single Unified Entrypoint*: Rejected due to risk of CSS rule cascades and unwanted global JS execution across unrelated views.
  - *Unbundled Public Copying*: Rejected because it bypasses Vite cache-busting, minify, and hot module replacement.

---

### 2. Self-Hosted Arabic Web Fonts Integration

- **Decision**: Download WOFF2 font files for Cairo/Tajawal into `resources/fonts/` and register `@font-face` rules inside `resources/css/app.css`.
- **Rationale**: Per clarification Q2, self-hosting fonts guarantees offline availability in WAMP local environments and eliminates external Google Fonts CDN network latency.
- **Alternatives Considered**:
  - *Google Fonts CDN Link*: Rejected due to offline development dependencies and potential network delays in local clinic deployments.
  - *System Font Stack Only*: Rejected due to inconsistent typography rendering across OS environments (Windows vs Linux vs macOS).

---

### 3. Reusable Blade Components Extraction (`resources/views/components/`)

- **Decision**: Extract recurring UI patterns from `pre-views/*.html` into anonymous Blade components under `resources/views/components/` (e.g., `<x-card>`, `<x-data-table>`, `<x-status-badge>`, `<x-search-filter>`, `<x-form-group>`).
- **Rationale**: Per clarification Q3, componentizing repeated HTML structures reduces layout duplication across the 8 mapped Blade views and establishes a consistent design system for Phases 3 through 9.
- **Alternatives Considered**:
  - *Raw Copy-Paste Markup*: Rejected because it creates massive code duplication and maintenance friction across 8 Blade views.
  - *Minimal Blank Scaffolding*: Rejected because it leaves downstream phases with missing layout structure.

---

### 4. ES Module Migration with Window Binding (`resources/js/shared-data.js`)

- **Decision**: Refactor `pre-views/shared-data.js` into an ES module located at `resources/js/shared-data.js`, exporting modular data functions while binding `window.WCCMS` on module initialization.
- **Rationale**: Per clarification Q4, Vite treats scripts as ES modules with module-level scope. Binding `window.WCCMS` maintains backward compatibility with legacy inline event handlers while providing standard ES `import` paths for modern JS modules.
- **Alternatives Considered**:
  - *Strict Non-Global ES Module*: Rejected because static event handlers in `pre-views/*.html` rely on global scope access.
  - *Unbundled Public Legacy Script*: Rejected because it bypasses Vite build optimization and asset versioning.

---

### 5. Laravel Arabic Localization Structure (`lang/ar/`)

- **Decision**: Abstract static layout text from `header.blade.php`, `sidebar.blade.php`, and `footer.blade.php` into Laravel translation files (`lang/ar/navigation.php`, `lang/ar/ui.php`) and render using `__('navigation.key')`.
- **Rationale**: Per clarification Q5, using Laravel language files complies with Principle IV (First-Class Arabic Support) and centralizes clinical UI terminology management.
- **Alternatives Considered**:
  - *Hardcoded Arabic Literals in Blade*: Rejected due to maintainability friction and lack of structured localization management.

---

### 6. Data Field ↔ Schema Consistency Mapping (`docs/ui-schema-mapping.md`)

- **Decision**: Produce `docs/ui-schema-mapping.md` prior to converting static pages into data-bound Blade views, utilizing per-page mapping tables (`[UI Field/Label]` → `[Table.Column]` → `[Enum Constraint Validation]`) followed by a consolidated Gap Summary section cross-referenced to Phase 1 open decisions.
- **Rationale**: Per User Story 4 & FR-007, auditing static UI labels against the Phase 1 DB schema prevents silent data model drift and catches schema gaps early before backend controller logic is built.
- **Alternatives Considered**:
  - *Ad-hoc Column Matching*: Rejected because unmapped fields risk being silently dropped or incorrectly named during controller implementation.
  - *Modifying DB Schema in Phase 2*: Rejected because Phase 2 is scoped strictly to UI foundation; genuine schema additions belong to Phase 1 specification amendments.

