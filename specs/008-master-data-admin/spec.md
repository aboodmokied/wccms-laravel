# Feature Specification: Master Data Administration

**Feature Branch**: `008-master-data-admin`  
**Created**: 2026-10-04  
**Status**: Draft  
**Input**: User description: "Phase 8 — Master Data Administration: Admin-only CRUD and activation toggling (IS_ACTIVE) for reference tables (Departments, Past Medical History, Diagnoses, Wound Types, Dressings, Medications, Procedures), preserving historical integrity without hard deletes."

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Master Data Workspace & RESTful Resource Endpoints (Priority: P1) 🎯 MVP

As an Administrator (`ADMIN`), I want to access dedicated RESTful management routes for each reference domain (`/admin/departments`, `/admin/pmh`, `/admin/diagnoses`, `/admin/wound-types`, `/admin/dressings`, `/admin/medications`, `/admin/procedures`), so that I can audit and manage all system reference tables in clean, structured RTL Arabic interfaces.

**Why this priority**: Administrators need a clear overview of active and inactive reference data across distinct, well-defined resource endpoints to maintain system accuracy.

**Independent Test**: Log in as an Admin user, navigate to each master data endpoint (e.g. `/admin/departments` or `/admin/medications`), and verify that items are correctly listed with active/inactive status badges, search filtering, and action controls.

**Acceptance Scenarios**:

1. **Given** an authenticated user with `ADMIN` role, **When** they navigate to any master data resource route (e.g. `/admin/medications`), **Then** the system displays the dedicated management view for that domain with navigation tabs linking to all other master data domains.
2. **Given** the Admin opens any master data resource page, **When** the page renders, **Then** the system displays a search-enabled table listing items with their code, Arabic name, status badge (`نشط / غير نشط`), and action controls.

---

### User Story 2 - Creating & Editing Reference Items (Priority: P1)

As an Administrator (`ADMIN`), I want to add new reference entries (e.g., adding a new medication or dressing type) or modify existing entry names, so that clinical options stay up to date with clinic practices.

**Why this priority**: Clinical operations expand over time; administrators must be able to introduce new reference terms without developer intervention.

**Independent Test**: Add a new reference item in any category, verify it persists in the database with a generated UUID primary key and `IS_ACTIVE = 1`, and test editing its name.

**Acceptance Scenarios**:

1. **Given** an Admin is on a master data resource page (e.g. `/admin/wound-types`), **When** they click "إضافة جديد" (Add New) and submit a valid unique item name, **Then** the system creates a new record in the corresponding `_tb` table with a generated UUID primary key and default active status (`IS_ACTIVE = 1`).
2. **Given** an Admin wants to correct a spelling error in a reference item, **When** they click "تعديل" (Edit) and submit an updated name, **Then** the system updates the item name and displays a success notification.
3. **Given** an Admin attempts to create or edit an item with a duplicate name within the same table, **When** the form is submitted, **Then** the system rejects the submission with a validation message ("الاسم مستخدم بالفعل").

---

### User Story 3 - Activation & Deactivation Toggle (No Hard Deletes) (Priority: P1)

As an Administrator (`ADMIN`), I want to activate or deactivate reference items rather than deleting them, so that historical patient records, past daily reviews, and diagnostic histories retain their integrity without foreign key violations.

**Why this priority**: Hard deleting reference records causes broken clinical histories or database foreign key errors. Soft activation toggling preserves historical audit trails while hiding inactive items from active clinical forms.

**Independent Test**: Deactivate an item (set `IS_ACTIVE = 0`), verify it changes status badge to "غير نشط", verify it is hidden from clinical form dropdowns (e.g. request creation or daily review modals), and verify past clinical records referencing it continue to display its name correctly.

**Acceptance Scenarios**:

1. **Given** an active reference item, **When** the Admin clicks "تعطيل" (Deactivate), **Then** the system updates its `IS_ACTIVE` column to `0` without deleting the row.
2. **Given** a deactivated reference item, **When** a nurse opens a clinical form (e.g. registration, daily review, diagnosis modal), **Then** the deactivated item does NOT appear in active selection dropdowns.
3. **Given** a historical clinical record references a deactivated item, **When** reviewing the patient's medical history or past daily review, **Then** the system still displays the item's name correctly from historical relations.
4. **Given** a deactivated item, **When** the Admin clicks "تفعيل" (Reactivate), **Then** the system updates its `IS_ACTIVE` column back to `1`, restoring it to active clinical dropdowns.

---

### User Story 4 - Strict Role-Based Access Control (RBAC) (Priority: P1)

As a system, I want to restrict all master data administration routes and actions exclusively to users with the `ADMIN` role, so that unauthorized personnel (e.g. `NURSE` or unassigned users) cannot alter clinic reference data.

**Why this priority**: Reference data governs system-wide clinical dropdowns; unauthorized edits threaten clinical data consistency and system integrity.

**Independent Test**: Attempt to access any `/admin/*` master data endpoint as a `NURSE` or unassigned user and verify HTTP 403 Forbidden response or redirect with access error.

**Acceptance Scenarios**:

1. **Given** an authenticated user with `NURSE` role or no role assigned, **When** they attempt to access `/admin/departments`, `/admin/medications`, or any master data POST/PUT/PATCH endpoints, **Then** the system denies access with HTTP 403 Forbidden or redirects with an authorization error.

---

### Edge Cases

- **What happens if an Admin tries to delete a reference item?** Hard deletion (`DELETE`) is completely disabled in the UI and API. The system only provides toggle actions for `IS_ACTIVE` (`0` or `1`).
- **What happens if an Admin submits a blank or whitespace-only name?** The system validates `required|string|max:255` and rejects empty inputs.
- **How are primary keys generated for new master data items?** Primary keys follow DB-First UUID rules (`CHAR(36)` uppercase primary keys like `WC_DEPT_CODE`, `WC_DIAG_CODE`, etc.) generated via `Str::uuid()`.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide dedicated Admin-only RESTful resource endpoints for all 7 reference domains:
  1. Departments (`/admin/departments` -> `wc_department_tb`)
  2. Past Medical History (`/admin/pmh` -> `wc_pmh_tb`)
  3. Diagnoses (`/admin/diagnoses` -> `wc_diagnosis_tb`)
  4. Wound Types (`/admin/wound-types` -> `wc_wound_type_tb`)
  5. Dressings (`/admin/dressings` -> `wc_dressing_tb`)
  6. Medications (`/admin/medications` -> `wc_medication_tb`)
  7. Procedures (`/admin/procedures` -> `wc_procedure_tb`)
- **FR-002**: System MUST restrict all master data management views and actions exclusively to users with `PER_USER_ROLE === 'ADMIN'`.
- **FR-003**: System MUST support creating new items with an auto-generated UUID primary key and default `IS_ACTIVE = 1`.
- **FR-004**: System MUST support updating the name of existing reference items while validating uniqueness within the same domain.
- **FR-005**: System MUST support toggling the active status (`IS_ACTIVE = 1` vs `IS_ACTIVE = 0`) for any reference item.
- **FR-006**: System MUST disallow hard deletion (`DELETE`) of reference records to protect historical relational integrity.
- **FR-007**: System MUST filter clinical dropdown menus across the application to present only active items (`IS_ACTIVE = 1`), while preserving deactivated items in historical logs.
- **FR-008**: System MUST support Arabic search filtering and pagination across all 7 master data domain tables.

### Key Entities & Database Schema Mapping

1. **`wc_department_tb`**: `WC_DEPT_CODE` (PK, CHAR 36), `WC_DEPT_NAME` (VARCHAR 255), `WC_DEPT_IS_ACTIVE` (SMALLINT/TINYINT)
2. **`wc_pmh_tb`**: `WC_PMH_CODE` (PK, CHAR 36), `WC_PMH_NAME` (VARCHAR 255), `WC_PMH_IS_ACTIVE` (SMALLINT/TINYINT)
3. **`wc_diagnosis_tb`**: `WC_DIAG_CODE` (PK, CHAR 36), `WC_DIAG_NAME` (VARCHAR 255), `WC_DIAG_IS_ACTIVE` (SMALLINT/TINYINT)
4. **`wc_wound_type_tb`**: `WC_WTYPE_CODE` (PK, CHAR 36), `WC_WTYPE_NAME` (VARCHAR 255), `WC_WTYPE_IS_ACTIVE` (SMALLINT/TINYINT)
5. **`wc_dressing_tb`**: `WC_DRESS_CODE` (PK, CHAR 36), `WC_DRESS_NAME` (VARCHAR 255), `WC_DRESS_IS_ACTIVE` (SMALLINT/TINYINT)
6. **`wc_medication_tb`**: `WC_MED_CODE` (PK, CHAR 36), `WC_MED_NAME` (VARCHAR 255), `WC_MED_IS_ACTIVE` (SMALLINT/TINYINT)
7. **`wc_procedure_tb`**: `WC_PROC_CODE` (PK, CHAR 36), `WC_PROC_NAME` (VARCHAR 255), `WC_PROC_IS_ACTIVE` (SMALLINT/TINYINT)

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin can search, create, update, or toggle status for any master data item in under 2 seconds.
- **SC-002**: 100% of deactivated items (`IS_ACTIVE = 0`) are immediately hidden from active clinical dropdowns while remaining 100% visible in historical records.
- **SC-003**: 0% accidental data loss: zero hard delete SQL statements exist for reference tables.
- **SC-004**: 100% RBAC security: 0 unauthorized non-admin users can access master data management routes.

---

## Clarifications

### Session 2026-10-04
- Q: How should the Web routes and controller endpoints for managing the 7 master data domains be structured? → A: Option B (Separate RESTful resource endpoints per domain: `/admin/departments`, `/admin/pmh`, `/admin/diagnoses`, `/admin/wound-types`, `/admin/dressings`, `/admin/medications`, `/admin/procedures`).

---

## Assumptions

- Reference items follow a uniform structure across all 7 tables: Primary Key (CHAR 36 UUID), Name (VARCHAR 255), and Active Flag (`IS_ACTIVE` SMALLINT default 1).
- All Eloquent models for master data specify `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`.
- Arabic language is the primary display language for reference item names.
