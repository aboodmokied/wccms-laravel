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
- **Authentication is externally delegated:** WCCMS does not implement its own login/credential storage. Identity is established by the hospital system. Provisioning policy is resolved: a first-time identity is auto-created in `PER_USERS_TB` with no role (`PER_USER_ROLE` = NULL) and zero access until an Admin explicitly assigns NURSE/ADMIN — onboarding convenience is subordinate to Actor vs. Data-Entry Separation (Principle above). The real transport mechanism (SSO/session/JWT/reverse-proxy header) is still pending confirmation from the hospital's technical team; identity resolution is built behind a swappable interface bound to a temporary mock driver in the meantime, so development is not blocked on that answer.

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
- **Amendment already applied:** `PER_USER_ROLE` was changed to nullable (with its CHECK constraint updated to allow NULL alongside `NURSE`/`ADMIN`), driven by the Phase 3 provisioning decision — a newly auto-provisioned identity has no role until an Admin assigns one. This is implemented in the current schema, not just planned.

---

## Phase 2 — UI Foundation: Static Pages → Blade
**New phase, added because you already have hand-built HTML/CSS/JS pages that need to become the app's actual views.**

- Set up Laravel's asset pipeline (Vite) to bundle the existing CSS/JS instead of linking them as static files.
- Extract whatever is repeated across your existing pages (header, nav, footer, sidebar) into a shared Blade layout (`layouts/app.blade.php`) with `@section`/`@yield` or Blade components — done once here, not re-derived per phase.
- Replace static `<link>`/`<script>` tags in the existing pages with `@vite(...)` directives pointing at the same CSS/JS files, unchanged in content.
- **Data ↔ schema consistency audit:** before converting any page, cross-reference every data field/label it displays against the Phase 1 DB schema (table + column + enum values). Produce a mapping document (e.g. `docs/ui-schema-mapping.md`) per page, and flag any field with no corresponding schema column as an open gap — this is documentation/audit only, not the actual data wiring (that still happens in the owning phase).
- **Inventory the existing pages and map each one to the phase that owns it** (e.g. login screen → Phase 3, request submission form → Phase 4, daily review screen → Phase 6). This phase only establishes the shared foundation (layout, asset pipeline, RTL handling, schema audit); converting each page's markup into a real Blade view wired to routes/controllers/data happens inside the phase that owns that page's feature — not all at once here.
- Solve Arabic/RTL layout handling once in the shared layout (ties to the RTL constitution principle), rather than per page later.

---

## Phase 3 — Auth & RBAC
**Revised: authentication is externally delegated to the hospital system (see the constitution note above) — this phase is narrower than a typical login feature, and its two open questions have been handled differently.**

- No local login form, password storage, or hashing. WCCMS trusts an identity resolved through a swappable `IdentityResolverContract`-style interface, currently bound to a temporary mock driver for development.
- **Resolved — provisioning:** a first-time resolved identity is auto-provisioned into `PER_USERS_TB` with no role (`PER_USER_ROLE` = NULL). Zero clinical access until an Admin explicitly assigns NURSE/ADMIN and links the identity to an employee profile in the same approval step. This was a deliberate choice to prioritize Actor vs. Data-Entry Separation over onboarding convenience.
- **Still open — transport:** the real mechanism (SSO redirect, shared session cookie, signed JWT, or trusted reverse-proxy header) is pending confirmation from the hospital's IT team; this is explicitly not this project's decision to make unilaterally. Work is not blocked on it, since resolution happens behind a swappable interface — replacing the mock later means adding one new class and flipping a config value, with no change to RBAC, provisioning, or downstream logic.
- Two WCCMS-local roles: NURSE and ADMIN, assigned by an Admin (not inferred from the hospital system), enforced via middleware at the route level.
- Access policies: who can submit a request, who can approve/reject, who can enter clinical data.
- If you have an existing static login/dashboard-shell page, repurpose it as the "resolving your session" / pending-approval / access-denied shell rather than a credential-entry form.

---

<!-- ## Phase 4 — Registration Requests
- Form for a nurse to submit a patient registration request (including searching/syncing patient data from the hospital system).
- Review flow: PENDING → APPROVED/REJECTED/CANCELLED, with rejection reason and notes.
- On approval: a trigger automatically creates a `WC_REGISTRATION_TB` record (this is the core of the database-first architecture).
- "My requests" view for nurses and "pending review" queue for supervisors — convert the matching existing static pages into Blade views wired to real data here. -->

## Phase 4 — Registration Requests

- **Hospital ward nurse → Wound Care Clinic referral workflow.** A nurse in the patient's current hospital department searches for the patient by National ID or name using the hospital patient data.

- The system displays the patient's current hospital admission information, including:
  - Patient name and National ID
  - Gender and date of birth
  - Current department/ward
  - Attending/supervising doctor
  - Hospital admission date

- The ward nurse submits a **Wound Care Clinic Registration Request** for the patient, including the reason for referral and optional notes.

- The request is stored with status `PENDING` and records:
  - The requesting hospital employee
  - The authenticated data-entry user
  - The patient
  - The patient's current hospital admission
  - The department/ward at the time of submission
  - Request timestamp
  - Patient snapshot information

- The request appears in the **Wound Care Clinic incoming requests queue**.

- A Wound Care Clinic nurse opens the request and visits the patient in the hospital department.

- When the clinic nurse starts handling the request, its status changes from `PENDING` to `VISIT_IN_PROGRESS`, recording the clinic employee responsible for the visit.

- During the patient visit, the Wound Care Clinic nurse completes the required initial registration information.

- The clinic nurse can:
  - Accept the patient for Wound Care Clinic registration, resulting in an `ACTIVE` registration.
  - Mark the request as `NOT_ELIGIBLE`, recording the reason.

- The request lifecycle is:

```text
PENDING
   ↓
VISIT_IN_PROGRESS
   ├── ACCEPTED → ACTIVE REGISTRATION
   └── NOT_ELIGIBLE
```

- A hospital ward nurse may cancel only their own request while it is still `PENDING`.

- The system must prevent duplicate pending requests when the patient already has a pending request, and must prevent registration when the patient already has an active Wound Care Clinic registration.

- Critical state transitions and duplicate-registration rules are enforced through database-side business logic in accordance with the project's Database-First Architecture.

- The request and resulting registration must maintain the distinction between the **clinical employee who performed the action** and the **user who entered the data**.

- Existing static pages related to request submission and request queues are converted into Blade views and integrated with the shared UI foundation.

- Detailed clinical registration data such as diagnoses, wounds, procedures, past medical history, dressings, and medications remains part of **Phase 5 — Registration & Diagnosis**.

---

## Phase 5 — Registration & Diagnosis
- View/edit the registration record: department, registration date, associated procedure, past medical history.
- Add one or more diagnoses/wounds per registration (wound type, initial dressing and medication).
- **Initial Wound Photo Documentation:** Capture/upload a baseline photo of the wound at initial diagnosis registration.
- Discharge flow: manual (by a nurse) or automatic (from the hospital system), recording who executed it.
- Convert the matching existing static pages here.

---

## Phase 5A — Longitudinal Patient Medical Record
**Status: implemented through Spec 011 (`specs/011-longitudinal-patient-record`).**

### Problem

The patient page currently presents the clinical record for the current registration only. A patient may have multiple Wound Care Clinic registrations across different periods, so previous registrations and their clinical history are not visible from the same patient record.

### Goal

Provide one longitudinal patient medical record that displays the patient's current registration and every previous registration in a clear chronological structure.

### Scope

- Load all registrations belonging to the patient, ordered from newest to oldest.
- Show the active/current registration first and distinguish it visually from discharged or historical registrations.
- Group clinical data by registration. Data from separate registrations must never be merged into one visit timeline.
- For every registration, display:
  - Registration date, status, department, responsible nurse, procedure, discharge date, and past medical history notes.
  - Diagnoses and wounds recorded during that registration.
  - Daily and scheduled reviews, ordered from newest to oldest within the registration.
  - Wound photos inside the review where they were documented, while preserving the initial baseline photo for comparison.
  - Pending, completed, cancelled, missed, or voided scheduled appointments where applicable.
- Expand the current registration by default and present previous registrations as collapsible historical sections to keep the page readable.
- Support patients who have no active registration but have one or more previous registrations.
- Preserve direct navigation to the detailed page for an individual registration.
- Keep actor and data-entry audit information attached to the correct registration and clinical event.
- Avoid N+1 queries and support pagination or incremental loading when a patient has a large registration history.

### Acceptance Scenarios

1. A patient with one active registration and multiple previous registrations sees all registrations, with the active registration first.
2. Opening a previous registration shows only its diagnoses, reviews, appointments, and wound photos.
3. Reviews remain ordered from newest to oldest inside each registration.
4. A patient with only historical registrations can still access their complete medical record.
5. A patient with a single registration sees the same complete record without empty historical sections.
6. The page does not duplicate reviews, photos, or diagnoses across registrations.

### Spec Kit Starting Point

Create a dedicated feature spec for the longitudinal patient record. The spec should define the patient-page information architecture, registration grouping and expansion behavior, query/loading strategy, authorization rules, and feature-test scenarios before implementation begins.

---

## Phase 5B — Structured Wound Assessment & Hospital Form Parity
**Status: hospital-form discovery captured — ready for a dedicated Spec Kit spec and clinical clarification.**

### Source Documents Reviewed

This phase is based on the hospital forms supplied on 2026-10-10:

- `First Visit.xlsx` — initial wound assessment form, including its `Wound Assessment Form` and `DAILY FOLLOW UP` worksheets.
- `wound care daily follow up.docx` — the hospital's repeated daily wound follow-up grid.

The forms establish an important clinical rule: **each wound-assessment sheet represents one diagnosis/wound**. A patient registration can contain multiple diagnoses, so patient and visit data must be recorded once while the structured assessment is repeated independently for every `WC_REG_DIAGNOSIS_TB` record.

### Problem

The system already supports multiple diagnoses per registration and one review-detail row per diagnosis. However, its current structured fields cover mainly wound type, diagnosis, free-text observation, dressing, medication, outcome, follow-up flag, and photos. The hospital forms contain additional clinical findings that currently can only be omitted or buried in free text, which prevents reliable comparison, validation, and reporting.

### Goal

Capture every clinically meaningful field from the hospital's initial and follow-up forms as structured, diagnosis-specific data, while preserving one shared visit header and reusing the same assessment workflow for daily and scheduled reviews.

### Required Clinical Dataset

#### Shared patient and encounter context — recorded once

- Patient name, identifier, date of birth, and gender from the patient record.
- Ward/department and encounter date/time.
- Primary diagnosis label and the list of registration diagnoses/wounds.
- Ward nurse, Wound Care Team clinician, doctor where applicable, and authenticated data-entry user.
- Allergies and relevant past medical history.
- Electronic attestation timestamp and clinical employee identity.

#### Initial assessment — repeated for every diagnosis/wound

- Wound type: pressure, surgical, puncture, crush, plus managed master-data values.
- Exudate amount: dry/none, low, medium, high.
- Exudate type: watery, cloudy, purulent, clear.
- Odor: none, slight, moderate, offensive.
- Pain assessment and whether pain occurs during dressing changes.
- Fever: none, low-grade, high.
- Wound-bed findings: granulation, necrotic tissue, slough, epithelialization, and other approved findings.
- Wound-margin findings: erythema, edema, delayed healing, pocketing, and other approved findings.
- Size in millimetres: length, width, and depth, stored as numeric values rather than combined text.
- Anatomical location: head, abdomen, back, legs, buttocks, feet, hand, forearm, plus clinically approved values and laterality when needed.
- Whether a swab was taken.
- Nutrition status: well nourished or malnourished.
- Baseline status: first visit, deteriorating, static, or improving.
- Relevant comorbidities: none, vascular disease, diabetes, hypertension, plus approved values.
- Treatment, initial dressing, medication, and baseline photos.
- Follow-up plan, next-visit date/frequency, and main objective for the next visit.

#### Daily and scheduled follow-up — repeated for every assessed diagnosis/wound

- Assessment date and time.
- Wound status: deteriorating, static, or improving.
- Wound-bed findings: granulation, necrotic tissue, slough, pocketing, and approved additional findings.
- Exudate amount and type.
- Odor.
- Pain level and pain during dressing changes.
- Dressing/procedure used: gauze, foam, antimicrobial, debridement with dressing, another documented option, or a managed master-data value.
- Medication, wound description, change since the prior assessment, outcome, follow-up requirement, and review photos.
- Clinical employee and data-entry user.

A fulfilled scheduled appointment must create the same daily-review header and diagnosis-specific assessment details, linked through `WC_DREV_SCHEDULED_REVIEW_CD`. Scheduled reviews must not introduce a second competing clinical-assessment schema.

### Data-Model Direction

- Keep `WC_REG_DIAGNOSIS_TB` as the stable identity of a wound/diagnosis across one registration.
- Keep `WC_DAILY_REVIEW_TB` as the visit header and `WC_REVIEW_DETAIL_TB` as the link between a visit and each assessed diagnosis.
- Add a reusable structured assessment entity, provisionally `WC_WOUND_ASSESSMENT_TB`, linked to:
  - `WC_RDIAG_CODE` for the owning diagnosis/wound.
  - Nullable `WC_REVD_CODE` for a follow-up assessment; null identifies the baseline/initial assessment.
  - Assessment context and timestamp, clinical employee, and data-entry user.
- Store scalar findings as typed columns with portable string codes and CHECK constraints: status, exudate amount/type, odor, pain level, pain-at-dressing-change flag, fever, dimensions, swab flag, nutrition status, treatment, and follow-up objective.
- Add a normalized multi-value finding structure, provisionally `WC_WOUND_ASSESSMENT_FINDING_TB`, for categories where more than one finding may be present, such as wound bed, wound margin, location, and clinically wound-specific findings. Do not serialize the core clinical dataset into opaque free text or database-specific JSON.
- Model registration-level comorbidities/allergies once and reference them from each wound assessment view rather than duplicating the same patient fact for every diagnosis.
- Use managed master data for wound types, dressings, locations, tissue findings, and other extensible clinical options when the values must be administratively configurable.
- Add new forward migrations; preserve current records and keep new structured fields nullable for legacy reviews until a deliberate backfill is defined.
- Enforce database-level ownership constraints so an assessment, review detail, photo, and diagnosis always belong to the same registration.
- Preserve the existing actor/data-entry separation on every new clinical record.

### Form and Workflow Changes

#### Initial registration

- Render patient and encounter context once at the top.
- Use a diagnosis repeater where each diagnosis card contains the complete initial hospital assessment dataset and its own baseline photos.
- Allow multiple diagnosis cards before acceptance and validate each card independently.
- Make treatment and follow-up planning visible within the owning diagnosis, while creating/linking a scheduled appointment when a next-visit date is entered.

#### Daily review

- Create one visit header, then render one assessment panel for each diagnosis selected for that visit.
- Show the latest assessment beside the new entry for clinical comparison.
- Save structured findings, dressing/medication, narrative changes, and photos under the correct review detail.
- Require an explicit “not assessed” reason if an active diagnosis is intentionally skipped, subject to clinical confirmation.

#### Scheduled review

- Reuse the daily-review form partial and validation contract.
- Lock the registration and appointment ownership from the selected scheduled review.
- Store the resulting clinical data in the same review/detail/assessment tables, then complete the appointment atomically.

#### Longitudinal record

- Display the structured findings per diagnosis at baseline and on every later review.
- Present dimensions and key findings as comparable values over time without mixing diagnoses or registrations.
- Continue placing baseline photos under the diagnosis and follow-up photos under their owning review.

### Compatibility and Migration

- Existing `WC_RDIAG_DESCRIPTION`, `WC_DREVD_WOUND_DESCRIPTION`, and `WC_DREVD_WOUND_CHANGES` remain available for narrative clinical context.
- Existing outcome, dressing, medication, appointment, photo, and audit data must continue to render.
- Do not infer structured findings from old free text automatically. Historical values remain “not recorded” unless a reviewed backfill mapping exists.
- Seed the report vocabulary with stable English codes and Arabic display labels while keeping it compatible with the later Oracle port.
- Add a source-to-schema mapping artifact during the Spec Kit planning phase so every hospital-form field has exactly one owner, type, allowed-value set, and UI location.

### Acceptance Scenarios

1. A registration containing three diagnoses stores three independent initial assessments while patient and encounter context is stored once.
2. Editing or reviewing one diagnosis never changes the findings, dimensions, photos, or treatment of another diagnosis.
3. A daily visit stores one header and a separate structured assessment for every assessed diagnosis.
4. Completing a scheduled appointment uses the same assessment fields and tables as a daily review and retains its appointment link.
5. Exudate, odor, pain, wound-bed findings, wound-margin findings, dimensions, location, swab, nutrition, treatment, and follow-up plan can be represented without relying only on narrative text.
6. Multiple simultaneous wound-bed or wound-margin findings can be retained when clinically valid.
7. The longitudinal record compares baseline and follow-up values for the same diagnosis only.
8. Existing registrations and reviews created before this phase remain readable and show missing structured fields as “not recorded.”
9. Clinical employee and data-entry user remain separately attributable for initial, daily, and scheduled assessments.
10. Database constraints reject a review assessment or photo attached to a diagnosis from another registration.

### Clinical Clarifications Required Before `/speckit-plan`

1. Confirm whether wound-bed and wound-margin options are multi-select and where `Pocketing` belongs, because the two supplied forms classify it differently.
2. Replace the paper's mixed pain choices with an approved representation: severity plus a separate “during dressing change” flag, or preserve the choices exactly.
3. Confirm whether location is single-select or multi-select and whether side/laterality and a free-text anatomical detail are mandatory.
4. Confirm whether allergies and comorbidities belong to the patient, hospital admission, registration, or wound assessment, and which external hospital values can be imported.
5. Define the distinct responsibilities of `Nurse`, `WCT`, and `DR` on the initial form and how each maps to hospital employees/users.
6. Confirm whether the authenticated clinical identity and timestamp replace the handwritten signature or whether a separate signature artifact is required.
7. Decide whether every active diagnosis must be assessed in every visit or whether a documented “not assessed” reason is sufficient.
8. Clarify whether `Debridement & dsg` is a procedure plus dressing rather than a dressing value, and map every paper option to the correct master-data table.
9. Confirm millimetres as the canonical storage unit and the required numeric precision for length, width, and depth.
10. Confirm whether exact printable replicas of the hospital forms are required or whether complete structured capture and a clinically equivalent report are sufficient.

### Spec Kit Starting Point

Create a dedicated feature spec from this phase. The specification must include the source-to-schema field matrix, the diagnosis-repeat workflow, unified initial/daily/scheduled assessment rules, clinical validation requirements, legacy-data behavior, authorization/audit requirements, and feature tests for multi-diagnosis isolation before implementation starts.

---

## Phase 6 — Daily Review
- "Today's follow-up visit" screen per registration: shows all of that registration's diagnoses/wounds at once (the visit header).
- For each wound: update description, observed changes, new dressing/medication, status assessment (improved+discharge / improved+follow-up / worsened / deceased), and whether it needs further follow-up.
- **Follow-up Wound Photo Documentation:** Capture/upload a progress photo of the wound at every daily review visit.
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

## Phase 10 — Wound Photo Documentation
- Capture/upload initial baseline wound photos during first diagnosis registration (`WC_PHOTO_STAGE = 'INITIAL'`).
- Capture/upload follow-up wound photos during every daily review visit (`WC_PHOTO_STAGE = 'REVIEW'`).
- Store images in `storage/app/public/wound_photos` and track metadata in `WC_WOUND_PHOTO_TB`.
- Interactive visual timeline and gallery component in registration details view for side-by-side photo comparison of wound healing progress over time.

---

## Phase 11 — Oracle Port
- Convert the migrations from MySQL (the local prototype) to real PL/SQL on Oracle.
- Write all the triggers mentioned above (request approval → create registration, improved+discharge → scheduled appointment, new registration → void old appointments) as real PL/SQL.
- Reconfigure Laravel to connect to Oracle (`yajra/laravel-oci8`) instead of MySQL.
- No Blade/UI work here — this phase is purely database and connection-layer.

---

## Phase 12 (optional) — Hospital System Integration
- Periodic or on-demand sync of patient data from the external hospital system (`MR_PAT_LAST_SYNCED_AT`).
- Receive an automatic discharge notification (`HOSPITAL_AUTO`) from the hospital system to update registration status.

---

## Dependency Order

```
Phase 1 (DB) → Phase 2 (UI Foundation) → Phase 3 (Auth) → Phase 4 (Requests) → Phase 5 (Registration)
                                                                                     ├──→ Phase 5A (Longitudinal Record)
                                                                                     └──→ Phase 5B (Structured Wound Assessment)
                                                                                                      ↓
                                                                         Phase 6 (Daily Review) → Phase 7 (Scheduled Review)
                                                                                     ↓
                                               Phase 8 (Master Data)   Phase 9 (Reports)   Phase 10 (Photos)
                                                                                     ↓
                                                      Phase 11 (Oracle Port) → Phase 12 (Hospital Integration)
```

Phase 5B defines the structured clinical dataset consumed by Phases 6 and 7. Phase 8, Phase 9, and Phase 10 can run in parallel with the rest of the clinical workflow once Phase 2 is done.
