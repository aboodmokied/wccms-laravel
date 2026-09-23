# Feature Specification: Phase 3 — Auth & RBAC

**Feature Branch**: `003-auth-rbac`
**Created**: 2026-09-21
**Status**: Finalized — all integration clarifications resolved (Session 2026-09-22)
**Input**: User description: "Phase 3 — Auth & RBAC"

## Architecture Note (added this revision)

Authentication identity is **externally delegated to the hospital system**. WCCMS does NOT implement its own login form, does not collect a password from the user, and does not validate credentials against `PER_USER_PASSWORD`. A user arrives at WCCMS already authenticated by the hospital system via a signed JWT session/token artifact. WCCMS's own responsibility is narrower than originally scoped:

1. Trust and resolve the incoming identity artifact on each request (middleware).
2. Provision or look up the corresponding local `PER_USERS_TB` record.
3. Enforce WCCMS-local RBAC (`NURSE`/`ADMIN`) on top of that resolved identity — roles are WCCMS-specific and not expected to come from the hospital system.
4. Maintain the Actor vs. Data-Entry linkage to `PER_EMPLOYEE_TB` as before.

Everything below reflects the finalized integration requirements from the clarification session.

## Clarifications

### Session 2026-09-22

- Q: How will the hospital system pass the authenticated user identity to WCCMS on each HTTP request? → A: Signed JWT bearer token or cookie issued by the hospital authentication system.
- Q: When a valid hospital identity visits WCCMS for the first time without an existing PER_USERS_TB record, how should WCCMS handle provisioning? → A: Auto-provision a PER_USERS_TB record with NULL role and inactive status (PER_USER_IS_ACTIVE = 0 / pending approval) requiring an Admin to assign a role and activate.
- Q: Where should unauthenticated or un-identity-resolved users be redirected when attempting to access WCCMS protected routes? → A: Redirect to an external hospital login portal URL configured via environment variable (APP_HOSPITAL_LOGIN_URL).
- Q: When a user logs out in WCCMS or their session expires, how should WCCMS handle local and hospital system logout? → A: Invalidate local WCCMS session only upon local logout or session expiration.
- Q: How should the unused PER_USER_PASSWORD column in PER_USERS_TB be handled in the database schema? → A: Retain PER_USER_PASSWORD as an unused column in PER_USERS_TB without altering existing database migrations.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Identity Resolution via Hospital-System Session (Priority: P1)

As a clinic staff member (Nurse or Admin), I access WCCMS after already being authenticated by the hospital system, so that I can use WCCMS features under my role without a separate WCCMS login step.

**Why this priority**: Identity resolution is the prerequisite gateway for all system operations and data access security — it replaces what was previously scoped as local login.

**Independent Test**: Can be tested by simulating requests with a valid, missing, and invalid/expired hospital session artifact and verifying WCCMS grants, denies, or redirects access accordingly.

**Acceptance Scenarios**:

1. **Given** a valid signed JWT bearer token or cookie issued by the hospital authentication system, **When** the user reaches any WCCMS route, **Then** WCCMS resolves the user's identity from that artifact and establishes a local application session without prompting for credentials.
2. **Given** a missing, invalid, or expired hospital session artifact, **When** the user reaches any WCCMS route, **Then** WCCMS denies access and redirects the browser to the hospital system's portal entry point configured via `APP_HOSPITAL_LOGIN_URL`.
3. **Given** a resolved identity with no matching `PER_USERS_TB` record, **When** first encountered, **Then** WCCMS auto-provisions a new `PER_USERS_TB` record with a `NULL` role and inactive status (`PER_USER_IS_ACTIVE` = 0), denying access with an Arabic pending-approval message ("حسابك قيد المراجعة والاعتماد من قبل مسؤول النظام") until an Admin assigns a role and activates the account.
4. **Given** a user's session with WCCMS ends (via local logout or session expiry), **When** WCCMS is next accessed, **Then** WCCMS invalidates its local application session without sending external hospital logout signals.
5. **Given** a WCCMS-local account with `PER_USER_IS_ACTIVE` = 0, **When** an otherwise-valid hospital identity resolves to that account, **Then** WCCMS denies access and displays an Arabic message stating WCCMS access specifically is disabled ("تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول") — this is a WCCMS-local override independent of the user's hospital-wide account status.

---

### User Story 2 - Role-Based Access Control & Action Authorization (Priority: P1)

*(Unchanged from the original spec — this remains entirely WCCMS-local regardless of how identity is established.)*

As the system administrator or clinic nurse, I want the system to strictly enforce my role permissions (`ADMIN` vs `NURSE`) across all pages and actions so that unauthorized users cannot execute restricted administrative or supervisor tasks.

**Why this priority**: Secures patient data and clinical governance by preventing non-admin staff from approving requests or modifying system master data.

**Independent Test**: Can be tested independently by resolving a session as `NURSE` and attempting to access admin-only URLs or perform admin actions (e.g. approving registration requests, editing dressing types), verifying that access is denied with an Arabic HTTP 403 authorization error.

**Acceptance Scenarios**:

1. **Given** a resolved identity with the `NURSE` role, **When** they attempt to access supervisor/admin screens (such as pending request approval queue or master data management), **Then** the system denies access with an Arabic authorization error message ("غير مصرح لك بالوصول إلى هذه الصفحة").
2. **Given** a resolved identity with the `ADMIN` role, **When** they access request review or master data management screens, **Then** the system permits full access to review, approve, reject, or edit master reference data.
3. **Given** a resolved identity with either the `NURSE` or `ADMIN` role, **When** they submit a patient registration request or enter a daily clinical review, **Then** the system allows the action and records the identity of the user performing the entry.
4. **Given** a request with no resolvable hospital identity, **When** they attempt to directly navigate to any protected route (dashboard, requests, reviews, admin), **Then** the system redirects them per User Story 1, Scenario 2.

---

### User Story 3 - Linkage of User Account to Employee Profile (Priority: P2)

*(Unchanged from the original spec.)*

As a clinic supervisor, I want every system user account (`PER_USERS_TB`) to be linked to an employee profile (`PER_EMPLOYEE_TB`) so that clinical actions accurately reflect the practitioner performing the care alongside the account recording the entry.

**Why this priority**: Fulfills Constitution Principle V (Actor vs. Data-Entry Separation) ensuring legal and clinical accountability for patient care records.

**Independent Test**: Can be tested by creating or updating a user account linked to an employee record (`PER_EMP_USER_CD`), performing a clinical action, and verifying that both user identity and employee profile are correctly linked and queryable.

**Acceptance Scenarios**:

1. **Given** a user account linked to an employee record, **When** the user's identity is resolved by WCCMS, **Then** the system resolves the associated employee record (full name, job title) and displays the employee's name in the header navbar.
2. **Given** a user account that is not yet linked to an employee profile, **When** an administrator views the employee or user management panel, **Then** the admin can associate the user account with an existing employee record.
3. **Given** a clinical data entry form, **When** a user records clinical information, **Then** the system records the resolved user account as the data-entry user (`PER_USERS_TB`) while resolving the default practitioner as the linked employee (`PER_EMPLOYEE_TB`).

---

### User Story 4 - Role Assignment & WCCMS Access Management (Priority: P3)

**Revised**: no longer "account management" in the credential sense (no password reset, no account creation with a password) — scoped to what WCCMS actually owns: role assignment, employee linkage, and the local active/inactive override.

As a system administrator, I want to manage WCCMS-local role assignments and access status for resolved user identities (assign `NURSE`/`ADMIN`, toggle WCCMS access active/inactive, link to an employee profile) so that I can control what clinic staff can do in WCCMS as personnel and responsibilities change.

**Why this priority**: Essential administrative utility for granting appropriate access as clinic staff roles change, independent of the hospital system's own account lifecycle.

**Independent Test**: Can be tested by an `ADMIN` toggling a resolved user's WCCMS access from active to inactive and verifying that the target user's active session is terminated and subsequent access is blocked, without affecting their hospital-system account.

**Acceptance Scenarios**:

1. **Given** an `ADMIN` user on the user management screen, **When** they view the list of resolved user identities known to WCCMS, **Then** the system displays each with its associated employee name, role (`ADMIN`/`NURSE`), and WCCMS access status (Active/Inactive).
2. **Given** an `ADMIN` user, **When** they change a user's role from `NURSE` to `ADMIN` or vice versa, **Then** the system updates the role and immediately applies updated access permissions on the user's next request.
3. **Given** an `ADMIN` user, **When** they set a user's WCCMS access status to Inactive, **Then** the system updates `PER_USER_IS_ACTIVE` to false and prevents any new WCCMS sessions from being established for that identity, independent of that person's standing in the hospital system itself.

---

### Edge Cases

- What happens if a resolved identity's account is deactivated (WCCMS-locally) by an Admin while a session is active? The next request must invalidate the session and redirect per User Story 1, Scenario 2, with a WCCMS-specific (not hospital-account) disabled message.
- What happens if a user account is not linked to any employee record (`PER_EMP_USER_CD` is NULL) when attempting to perform a clinical action? The system falls back safely, recording the user account ID while alerting the user/admin that employee profile association is required for clinical signing.
- What happens if the hospital identity artifact does not carry any information WCCMS could use to infer a role? Roles remain entirely WCCMS-local — an Admin must assign `NURSE`/`ADMIN` on first provisioning; WCCMS does not attempt to infer role from hospital-side data.
- What happens if the hospital session artifact is present but its issuer/signature cannot be validated? WCCMS denies access, invalidates any existing local session, and logs a security warning for invalid/tampered JWT signature.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST resolve the authenticated user's identity from a signed JWT bearer token or cookie issued by the hospital authentication system via dedicated middleware on each request, without collecting or validating a password locally.
- **FR-002**: System MUST maintain a WCCMS-local `PER_USER_IS_ACTIVE` override and enforce it during identity resolution, independent of the user's status in the hospital system.
- **FR-003**: System MUST support distinct WCCMS-local roles (`ADMIN` and `NURSE`) stored in `PER_USER_ROLE`, assigned by a WCCMS Admin rather than inferred from the hospital system.
- **FR-004**: System MUST enforce Role-Based Access Control via middleware to restrict administrative routes and actions exclusively to users with `PER_USER_ROLE` = 'ADMIN'.
- **FR-005**: System MUST allow both `NURSE` and `ADMIN` roles to access patient request creation, clinical daily review forms, and personal dashboard views.
- **FR-006**: System MUST restrict registration request review (approving/rejecting) and master data editing (departments, dressings, medications, diagnoses) to users with `ADMIN` role.
- **FR-007**: System MUST link `PER_USERS_TB` records to `PER_EMPLOYEE_TB` records via `PER_EMP_USER_CD` foreign key.
- **FR-008**: System MUST display the authenticated employee's full name (`PER_EMP_FULL_NAME`) and role in the top header navbar upon successful identity resolution.
- **FR-009**: System MUST protect all non-public web routes with identity-resolution middleware, redirecting requests with no valid hospital identity artifact to the hospital system's entry point URL (`APP_HOSPITAL_LOGIN_URL`).
- **FR-010**: System MUST provide a WCCMS-local session termination mechanism that invalidates the local application session upon logout or expiration without sending external hospital logout signals.
- **FR-011**: System MUST render all authorization and access-status messages in Arabic with native RTL support.
- **FR-012 (new)**: `PER_USER_PASSWORD` is retained in the Phase 1 schema for structural stability without schema modification, but MUST NOT be written to or read by WCCMS application logic, since credentials are never handled locally.

### Key Entities *(include if feature involves data)*

- **User Account (`PER_USERS_TB`)**:
  - `PER_USER_CODE` (PK, UUID string)
  - `PER_USER_USERNAME` (Unique string — now a resolved identifier from the hospital identity artifact, not a login field the user types)
  - `PER_USER_PASSWORD` (Unused by WCCMS — see FR-012)
  - `PER_USER_ROLE` (Enum string: `ADMIN`, `NURSE` — WCCMS-local assignment, nullable upon auto-provisioning until assigned by Admin)
  - `PER_USER_IS_ACTIVE` (Boolean flag: WCCMS-local access override, 1 = Active, 0 = Inactive)
- **Employee Profile (`PER_EMPLOYEE_TB`)**:
  - `PER_EMP_CODE` (PK, UUID string)
  - `PER_EMP_FULL_NAME` (Arabic string)
  - `PER_EMP_JOB_TITLE` (Arabic string, e.g. "ممرض جروح", "طبيب مسؤول")
  - `PER_EMP_USER_CD` (FK to `PER_USERS_TB.PER_USER_CODE`, nullable/unique)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Resolved, valid identities render the dashboard within 1.5 seconds of arrival with no additional credential step.
- **SC-002**: 100% of unauthorized route navigation and API/form submission attempts by `NURSE` role users to `ADMIN`-only actions are blocked with HTTP 403.
- **SC-003**: 100% of WCCMS-locally-deactivated accounts (`PER_USER_IS_ACTIVE` = 0) are blocked from establishing a WCCMS session, even with a valid hospital identity artifact.
- **SC-004**: 100% of resolved sessions display accurate Arabic employee identity in the header when linked to an employee profile.

## Assumptions

- Identity is established by an external hospital system via signed JWT session artifacts; WCCMS does not implement its own login form or password storage.
- All integration parameters (JWT bearer/cookie transport, auto-provisioning with NULL role and pending approval state, `APP_HOSPITAL_LOGIN_URL` redirect, local session termination, and schema preservation) have been fully clarified and finalized.
- Roles (`ADMIN`/`NURSE`) are WCCMS-local and are assigned by a WCCMS Admin upon account approval/activation.
- Throttling/brute-force protections from the original spec are removed from WCCMS's scope, since WCCMS never collects a password to brute-force — any such protection belongs to the hospital system's own login surface.