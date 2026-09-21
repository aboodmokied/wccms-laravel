# Layout & UI Component Model: Phase 2 — UI Foundation: Static Pages → Blade

**Feature Branch**: `002-ui-foundation-blade`
**Date**: 2026-09-20
**Spec**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

---

## UI Layout & Component Architecture

Phase 2 focuses on converting hand-crafted static HTML assets from `pre-views/` into structured Laravel Blade layouts, partials, components, Vite bundles, and Arabic localization dictionaries. No database schema or Eloquent model changes are introduced in this phase.

---

## 1. Master Layout & Partials Structure

```
resources/views/
├── layouts/
│   └── app.blade.php               # Central Master Layout (dir="rtl", lang="ar", @vite, slots)
├── partials/
│   ├── header.blade.php            # User navbar, notification indicator, clinic branding
│   ├── sidebar.blade.php           # Phase 3-9 clinical module navigation links
│   └── footer.blade.php            # Copyright, version info, system status
└── components/
    ├── card.blade.php              # Shared content container panel
    ├── data-table.blade.php        # Tabular data display wrapper
    ├── status-badge.blade.php      # Registration/Review state pills (Active, Pending, Discharged)
    ├── search-filter.blade.php     # Standardized patient search & filter bar
    └── form-group.blade.php        # Input wrapper with Arabic labels & validation feedback
```

---

## 2. Template Inventory & Phase Ownership Matrix

| Static Source (`pre-views/`) | Blade Target Path (`resources/views/`) | Route / Placeholder | Owning Phase | Purpose |
|------------------------------|----------------------------------------|---------------------|--------------|---------|
| `index.html` | `auth/login.blade.php` <br> `dashboard.blade.php` | `/login` <br> `/dashboard` | Phase 3 | Authentication & Main Shell |
| `requests.html` | `requests/index.blade.php` | `/requests` | Phase 4 | Registration Requests Queue |
| `registered-patients.html` | `registrations/index.blade.php` | `/registrations` | Phase 5 | Active Patient Registrations |
| `complete-registration.html` | `registrations/create.blade.php` | `/registrations/create` | Phase 5 | Registration & Diagnosis Entry |
| `daily-review.html` | `reviews/daily.blade.php` | `/reviews/daily` | Phase 6 | Daily Review Visit Assessment |
| `scheduled-reviews.html` | `reviews/scheduled.blade.php` | `/reviews/scheduled` | Phase 7 | Appointment Calendar & Invalidation |
| `admin.html` | `admin/index.blade.php` | `/admin` | Phase 8 | Master Reference Data Management |
| `patient-history.html` | `reports/history.blade.php` | `/reports/history` | Phase 9 | Clinical History & Dashboards |

---

## 3. Asset Pipeline Structure (`resources/` & `vite.config.js`)

```
resources/
├── css/
│   ├── app.css                     # Global styles, RTL reset, @font-face rules
│   └── pages/
│       ├── auth.css                # Login / Shell page styles
│       ├── requests.css            # Registration requests table styles
│       ├── registrations.css       # Registered patients list styles
│       ├── reviews.css            # Daily & scheduled review form styles
│       └── admin.css              # Master data admin table styles
├── js/
│   ├── app.js                      # Core JS entrypoint (bootstraps shared-data module)
│   ├── shared-data.js              # ES module exporting data helpers & binding window.WCCMS
│   └── pages/
│       ├── requests.js             # Client-side request queue handlers
│       ├── reviews.js              # Review modal & form interaction handlers
│       └── admin.js                # Master data filter & modal handlers
└── fonts/
    └── cairo/                      # Self-hosted WOFF2/WOFF Arabic font files
        ├── cairo-v28-arabic-regular.woff2
        └── cairo-v28-arabic-700.woff2
```

---

## 4. Arabic Localization Dictionaries (`lang/ar/`)

```
lang/ar/
├── navigation.php                  # Sidebar & header links (الرئيسية, طلبات التسجيل, المرضى المسجلين...)
├── ui.php                          # Common UI labels, buttons, search placeholders (حفظ, إلغاء, بحث...)
└── status.php                      # Clinical workflow state labels (قيد الانتظار, نشط, مكتمل, ملغى)
```

---

## 5. Data Field ↔ Schema Mapping Document (`docs/ui-schema-mapping.md`)

```
docs/
└── ui-schema-mapping.md            # Mandatory cross-reference audit file
```

### Document Schema Structure:

```markdown
# UI Data Field ↔ Database Schema Mapping

## Page: pre-views/requests.html (Phase 4 — Requests Queue)

| UI Label / Field Name | Target Table | Target Column | Column Type | Enum CHECK Constraint Match | Status |
|-----------------------|--------------|---------------|-------------|-----------------------------|--------|
| رقم الطلب / Request Code | `WC_REQUEST_REG_TB` | `WC_REQ_CODE` | `char(36)` | N/A (UUID PK) | Matched |
| اسم المريض / Patient Name | `MR_PATIENT_TB` | `MR_PAT_FULL_NAME` | `varchar(150)` | N/A | Matched |
| حالة الطلب / Status | `WC_REQUEST_REG_TB` | `WC_REQ_STATUS` | `varchar(20)` | `PENDING \| APPROVED \| REJECTED \| CANCELLED` | Matched |
| مراجعة بواسطة / Reviewed By | NONE | NONE | NONE | NONE | Gap (No Column) |

## Gap Summary & Proposed Phase 1 Amendments

| Unmatched UI Field | Affected Template | Affected Phase | Proposed Resolution | Phase 1 Decision Link |
|-------------------|-------------------|----------------|---------------------|-----------------------|
| Reviewed By | `requests.html` | Phase 4 | Document gap; add `WC_REQ_REVIEWED_BY_CD` in Phase 1 amendment | Decision #4 |
```

