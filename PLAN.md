# WCCMS Project Blueprint — Phased, One Spec per Phase

Wound Care Clinic Management System (WCCMS) — Laravel + Oracle, database-first architecture (business logic lives in triggers/stored procedures/views), built for Al-Shifa Hospital under the Palestinian Ministry of Health.

This blueprint is built on the decisions already made in the database design work (naming protocol, employee/user split, 1:1 merges, header/detail split for reviews). Each phase below is meant to become one independent Spec-Kit spec.

---

## Before the phases: `/constitution`

Before your first `/specify`, lock these principles into the constitution since they apply across every phase:

- **Stack:** Laravel (PHP) + Oracle SQL Server. Every phase assumes this stack.
- **Database-first architecture:** Critical business logic (approving requests, creating registrations, scheduling reviews, voiding them) lives in triggers/stored procedures/views inside the database, not just in the Laravel layer. Eloquent models reflect the structure; they don't replace the logic — except for simple reference/master-data CRUD, which is exempt.
- **Naming protocol:** `<PREFIX>_<name>_TB` for tables, `<short_prefix>_` for columns, `CODE` for the PK, `_CD` for FKs.
- **Arabic/RTL support:** Any UI or text content must support Arabic as a first-class language.
- **Actor vs. data-entry separation:** Any feature involving "who performed a clinical action" must distinguish the actual employee (`PER_EMPLOYEE_TB`) from the user who entered the data (`PER_USERS_TB`).
- **MySQL-first, Oracle-later:** Development happens against MySQL locally; the schema is designed to port cleanly (see Phase 1 and Phase 10). Use Laravel's fluent schema-builder migrations rather than raw SQL wherever possible. Triggers/stored procedures written in MySQL during development are executable specs of the logic — they get rewritten by hand in PL/SQL during the Oracle port, not auto-ported.
- **Existing static UI assets are the design source of truth:** You already have hand-built HTML/CSS/JS pages for (some of) the product. These are not thrown away and rebuilt from scratch — they're converted into Blade views incrementally, phase by phase (see Phase 2).

---

## Phase 1 — Core Data Model
**Status: largely complete from this conversation — ready to become the first spec.**

- 16 tables: users, employees, patients, registration requests, registrations (merged with procedure and past medical history), registration diagnoses, daily review (header + detail), scheduled reviews, and 7 master-data (reference) tables.
- Migrations written to port cleanly to Oracle later (UUIDs as CHAR(36), no native ENUM, no native BOOLEAN).
- Seeders for reference data (departments, medications, dressings, wound types, procedures, diagnoses, past medical history options).
- **Open decisions to resolve before finalizing this spec:**
  1. Snapshot columns on the registration-request table (a copy of patient data at submission time).
  2. A `reviewed_by` column on the request table (who approved/rejected).
  3. The unclear `VISIT_SERIAL` field on scheduled reviews.
  4. Whether to add a new `VOIDED` enum value, or reuse `CANCELLED`, for automatic invalidation on re-registration.
  5. A column tracking who recorded each diagnosis on `WC_REG_DIAGNOSIS_TB`.

---

## Phase 2 — UI Foundation: Static Pages → Blade
**New phase, added because you already have hand-built HTML/CSS/JS pages (inside pre-views folder) that need to become the app's actual views.**

- Set up Laravel's asset pipeline (Vite) to bundle the existing CSS/JS instead of linking them as static files.
- Extract whatever is repeated across your existing pages (header, nav, footer, sidebar) into a shared Blade layout (`layouts/app.blade.php`) with `@section`/`@yield` or Blade components — done once here, not re-derived per phase.
- Replace static `<link>`/`<script>` tags in the existing pages with `@vite(...)` directives pointing at the same CSS/JS files, unchanged in content.
- **Inventory the existing pages and map each one to the phase that owns it** (e.g. login screen → Phase 3, request submission form → Phase 4, daily review screen → Phase 6). This phase only establishes the shared foundation (layout, asset pipeline, RTL handling); converting each page's markup into a real Blade view wired to routes/controllers/data happens inside the phase that owns that page's feature — not all at once here.
- Solve Arabic/RTL layout handling once in the shared layout (ties to the RTL constitution principle), rather than per page later.

---

## Phase 3 — Auth & RBAC
- User login (`PER_USERS_TB`) — Laravel Sanctum or Breeze depending on whether you need an API or a web UI.
- Two roles: NURSE and ADMIN, enforced via middleware at the route level.
- Link a user account to an employee record (`PER_EMP_USER_CD`) on first login or from the admin panel.
- Access policies: who can submit a request, who can approve/reject, who can enter clinical data.
- If you have an existing static login/dashboard-shell page, convert it into a real Blade view here using the Phase 2 foundation.

---

## Phase 4 — Registration Requests
- Form for a nurse to submit a patient registration request (including searching/syncing patient data from the hospital system).
- Review flow: PENDING → APPROVED/REJECTED/CANCELLED, with rejection reason and notes.
- On approval: a trigger automatically creates a `WC_REGISTRATION_TB` record (this is the core of the database-first architecture).
- "My requests" view for nurses and "pending review" queue for supervisors — convert the matching existing static pages into Blade views wired to real data here.

---

## Phase 5 — Registration & Diagnosis
- View/edit the registration record: department, registration date, associated procedure, past medical history.
- Add one or more diagnoses/wounds per registration (wound type, initial dressing and medication).
- Discharge flow: manual (by a nurse) or automatic (from the hospital system), recording who executed it.
- Convert the matching existing static pages here.

---

## Phase 6 — Daily Review
- "Today's follow-up visit" screen per registration: shows all of that registration's diagnoses/wounds at once (the visit header).
- For each wound: update description, observed changes, new dressing/medication, status assessment (improved+discharge / improved+follow-up / worsened / deceased), and whether it needs further follow-up.
- When "improved+discharge" is assessed at the registration level: a trigger automatically creates a scheduled review appointment (empty, awaiting the visit).
- Convert the matching existing static pages here.

---

## Phase 7 — Scheduled Reviews
- Calendar/list of scheduled appointments per registration.
- When the patient shows up for their appointment: create an actual daily-review visit linked to the appointment (`WC_DREV_SCHEDULED_REVIEW_CD`), and update the appointment's status to COMPLETED.
- Missed (MISSED) and manually cancelled (CANCELLED) states.
- **Automatic invalidation trigger:** when a patient is registered again at the clinic, all of their pending appointments tied to the old registration are automatically voided.
- Convert the matching existing static pages here.

---

## Phase 8 — Master Data Administration
- CRUD screens (ADMIN role only) for the seven reference tables: departments, past medical history, diagnoses, wound types, dressings, medications, procedures.
- Activate/deactivate (`IS_ACTIVE`) instead of hard delete, to preserve the integrity of historical records that reference them.
- Convert the matching existing static admin pages here.

---

## Phase 9 — Reporting & Dashboards
- Metrics: number of active patients, average treatment duration, distribution of follow-up outcomes (improved/worsened/deceased), missed-appointment rate.
- Oracle views/stored procedures feeding these dashboards (consistent with the database-first architecture).
- Convert any existing static dashboard/report pages here.

---

## Phase 10 — Oracle Port
- Convert the migrations from MySQL (the local prototype) to real PL/SQL on Oracle.
- Write all the triggers mentioned above (request approval → create registration, improved+discharge → scheduled appointment, new registration → void old appointments) as real PL/SQL.
- Reconfigure Laravel to connect to Oracle (`yajra/laravel-oci8`) instead of MySQL.
- No Blade/UI work here — this phase is purely database and connection-layer.

---

## Phase 11 (optional) — Hospital System Integration
- Periodic or on-demand sync of patient data from the external hospital system (`MR_PAT_LAST_SYNCED_AT`).
- Receive an automatic discharge notification (`HOSPITAL_AUTO`) from the hospital system to update registration status.

---

## Dependency Order

```
Phase 1 (DB) → Phase 2 (UI Foundation) → Phase 3 (Auth) → Phase 4 (Requests) → Phase 5 (Registration)
                                                                                     ↓
                                                     Phase 6 (Daily Review) → Phase 7 (Scheduled Review)
                                                                                     ↓
                                               Phase 8 (Master Data)   Phase 9 (Reports)
                                                                                     ↓
                                                      Phase 10 (Oracle Port) → Phase 11 (Hospital Integration)
```

Phase 8 and Phase 9 can run in parallel with the rest of the work once Phase 2 is done, since they don't depend on the clinical workflow itself, but they still need the shared Blade layout from Phase 2 first.