# Implementation Plan: Phase 2 — UI Foundation: Static Pages → Blade

**Branch**: `002-ui-foundation-blade` | **Date**: 2026-09-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/002-ui-foundation-blade/spec.md`

---

## Summary

Migrate hand-crafted static HTML pages and CSS/JS assets from `pre-views/` into a robust, componentized Laravel Blade frontend foundation under Laravel 11's Vite asset pipeline. The plan establishes a central master layout (`layouts/app.blade.php`) enforcing first-class Arabic/RTL support (`dir="rtl"`, `lang="ar"`), self-hosted Arabic web fonts (Cairo), modular layout partials (header, sidebar, footer), reusable Blade components (`<x-card>`, `<x-data-table>`, etc.), ES module JavaScript refactoring with `window.WCCMS` global binding, and Arabic translation dictionaries (`lang/ar/`). All 8 static pages in `pre-views/` are inventoried and mapped to skeleton Blade views assigned to downstream feature phases (Phases 3–9). Additionally, a comprehensive Data Field ↔ Schema mapping document (`docs/ui-schema-mapping.md`) is produced to audit 100% of static UI labels against the Phase 1 DB schema prior to template conversion.

---

## Technical Context

**Language/Version**: PHP 8.3 / Node.js 18+

**Primary Dependencies**: Laravel 11, Vite (`laravel-vite-plugin`)

**Storage**: MySQL (`wccms` database - DB-First baseline)

**Testing**: PHPUnit (`php artisan test --compact`), Vite build verification (`npm run build`), & Schema audit validation (`docs/ui-schema-mapping.md`)

**Target Platform**: WAMP Server / Local Web Server (Windows)

**Project Type**: Web Application (Laravel Blade + Vanilla CSS/JS compiled via Vite)

**Performance Goals**: `npm run build` asset compilation < 5s; page load < 200ms

**Constraints**: First-class Arabic & RTL layout (`dir="rtl"`, `lang="ar"`), self-hosted Arabic web fonts (`resources/fonts/cairo`), Database-First architectural alignment, 100% pre-views static template coverage, and 100% UI label schema audit coverage.

**Scale/Scope**: 8 static HTML pages mapped to 8 Blade target views with reusable components, layout partials, and 1 schema mapping document.

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Principle I: Stack Assumption**: PASS. Uses Laravel (PHP) + Blade + Vite pipeline.
- **Principle II: Database-First Architecture**: PASS. Layout phase creates frontend Blade structures and documents schema mapping without altering DB schemas or bypassing stored procedure rules.
- **Principle III: Unified Database Naming Protocol**: PASS. UI components and schema audit align with existing `*_TB` entity schemas.
- **Principle IV: First-Class Arabic & RTL Support**: PASS. `dir="rtl"`, `lang="ar"`, self-hosted Cairo web fonts, and `lang/ar/` localization dictionaries enforced.
- **Principle V: Actor vs. Data-Entry Separation**: PASS. Header partial layout accommodates dual user indicator placeholders.
- **Principle VI: MySQL-First, Oracle-Later Development**: PASS. Standard Laravel Blade/Vite structure compatible across database drivers.

*All Constitution Gates PASSED.*

---

## Project Structure

### Documentation (this feature)

```text
specs/002-ui-foundation-blade/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (this file)
├── research.md          # Phase 0 research & design decisions
├── data-model.md        # Phase 1 UI layout & asset model
├── quickstart.md        # Phase 1 quickstart & validation guide
└── checklists/
    └── requirements.md  # Spec quality checklist

docs/
└── ui-schema-mapping.md # Data Field ↔ Schema consistency mapping audit
```

### Source Code Layout

```text
resources/
├── css/
│   ├── app.css                     # Global CSS, RTL reset, @font-face rules
│   └── pages/                      # Modular page-specific stylesheets
│       ├── auth.css
│       ├── requests.css
│       ├── registrations.css
│       ├── reviews.css
│       └── admin.css
├── js/
│   ├── app.js                      # Core JS entrypoint
│   ├── shared-data.js              # ES module exporting data helpers + window.WCCMS binding
│   └── pages/                      # Modular page-specific script handlers
│       ├── requests.js
│       ├── reviews.js
│       └── admin.js
├── fonts/
│   └── cairo/                      # Self-hosted Cairo WOFF2 fonts
├── views/
│   ├── layouts/
│   │   └── app.blade.php           # Master layout wrapper (dir="rtl", lang="ar")
│   ├── partials/
│   │   ├── header.blade.php        # Header navbar partial
│   │   ├── sidebar.blade.php       # Clinical module navigation sidebar partial
│   │   └── footer.blade.php        # Footer partial
│   ├── components/
│   │   ├── card.blade.php          # Component: Content panel card
│   │   ├── data-table.blade.php    # Component: Tabular data wrapper
│   │   ├── status-badge.blade.php  # Component: Status pill badge
│   │   ├── search-filter.blade.php # Component: Search filter bar
│   │   └── form-group.blade.php    # Component: Input form group
│   ├── auth/
│   │   └── login.blade.php         # Mapped from index.html (Phase 3)
│   ├── dashboard.blade.php         # Mapped from index.html (Phase 3)
│   ├── requests/
│   │   └── index.blade.php         # Mapped from requests.html (Phase 4)
│   ├── registrations/
│   │   ├── index.blade.php         # Mapped from registered-patients.html (Phase 5)
│   │   └── create.blade.php        # Mapped from complete-registration.html (Phase 5)
│   ├── reviews/
│   │   ├── daily.blade.php         # Mapped from daily-review.html (Phase 6)
│   │   └── scheduled.blade.php     # Mapped from scheduled-reviews.html (Phase 7)
│   ├── admin/
│   │   └── index.blade.php         # Mapped from admin.html (Phase 8)
│   └── reports/
│       └── history.blade.php       # Mapped from patient-history.html (Phase 9)
lang/
└── ar/
    ├── navigation.php              # Arabic navigation strings
    ├── ui.php                      # Common Arabic UI labels
    └── status.php                  # Workflow status labels
routes/
└── web.php                         # Skeleton route definitions for testing layout views
vite.config.js                      # Vite multi-entrypoint configuration
```

**Structure Decision**: Single Laravel application layout with modular Vite frontend asset pipeline and standard `resources/views/` directory hierarchy.

---

## Complexity Tracking

> *No Constitution violations present; no gate exceptions requested.*
