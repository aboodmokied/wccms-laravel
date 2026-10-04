# Phase 0: Research & Technical Decisions — Master Data Administration

**Feature Branch**: `008-master-data-admin`  
**Date**: 2026-10-04  
**Spec**: [spec.md](./spec.md)

---

## 1. RESTful Endpoints & Controller Architecture

### Decision
Use 7 dedicated RESTful resource controllers under `App\Http\Controllers\Admin` matching the 7 master data domain tables:
1. `DepartmentController` -> `GET|POST|PUT /admin/departments`, `PATCH /admin/departments/{department}/toggle`
2. `PmhController` -> `GET|POST|PUT /admin/pmh`, `PATCH /admin/pmh/{pmh}/toggle`
3. `DiagnosisController` -> `GET|POST|PUT /admin/diagnoses`, `PATCH /admin/diagnoses/{diagnosis}/toggle`
4. `WoundTypeController` -> `GET|POST|PUT /admin/wound-types`, `PATCH /admin/wound-types/{woundType}/toggle`
5. `DressingController` -> `GET|POST|PUT /admin/dressings`, `PATCH /admin/dressings/{dressing}/toggle`
6. `MedicationController` -> `GET|POST|PUT /admin/medications`, `PATCH /admin/medications/{medication}/toggle`
7. `ProcedureController` -> `GET|POST|PUT /admin/procedures`, `PATCH /admin/procedures/{procedure}/toggle`

### Rationale
- Directly implements Option B chosen during `/speckit-clarify`.
- Leverages standard Laravel Resource route structure (`index`, `store`, `update`, plus custom `toggleActive` action).
- Maintains strict Separation of Concerns and clean testability per domain.

### Alternatives Considered
- Single monolithic `MasterDataController` with dynamic `?domain=` query parameters: Rejected per user preference during clarification session.

---

## 2. Eloquent CRUD & DB-First Model Rules

### Decision
Use standard Eloquent model queries and methods (`create()`, `update()`, `paginate()`) while respecting DB-First model constraints:
- `$table = 'wc_*_tb'`
- `$primaryKey = 'PREFIX_CODE'`
- `$incrementing = false`
- `$keyType = 'string'`
- `$timestamps = false`
- Primary keys generated using `(string) Str::uuid()`.

### Rationale
- Constitution Principle II explicitly exempts simple reference/master data administration from in-database stored procedure requirements because reference data changes carry no clinical side effects.
- Aligns with existing master data Eloquent models (`WcDepartment`, `WcPmh`, `WcDiagnosis`, `WcWoundType`, `WcDressing`, `WcMedication`, `WcProcedure`).

---

## 3. Disabling Hard Deletes (Soft Activation Toggling)

### Decision
Hard deletion (`destroy` / `delete`) is explicitly omitted from routes, controllers, and UI. Items are deactivated or reactivated via `toggleActive` endpoint which updates `IS_ACTIVE = 0` or `1`.

### Rationale
- Prevents database foreign key constraint failures (`1451 Cannot delete or update a parent row`).
- Ensures historical clinical registrations (`wc_registration_tb`), diagnosis links (`wc_reg_diagnosis_tb`), and daily review details (`wc_review_detail_tb`) retain valid reference lookups when viewing patient histories.

---

## 4. RBAC Authorization Strategy

### Decision
Enforce `role:ADMIN` middleware on all `/admin/*` master data route groups.

### Rationale
- Prevents non-admin users (`NURSE` or unassigned roles) from modifying reference options.
- Integrates cleanly with existing `HospitalJwtAuthMiddleware` and `CheckRole` middleware.
