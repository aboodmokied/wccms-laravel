# Research & Technical Decisions: Phase 3 — Auth & RBAC

**Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md)

This document records technical research and architectural decisions for Phase 3 Auth & RBAC in WCCMS.

---

## 1. Identity Resolution & JWT Validation

### Decision
Implement identity resolution via a custom HTTP middleware (`HospitalJwtAuthMiddleware`). The middleware inspects incoming requests for a signed JWT artifact (checking both the `Authorization: Bearer <token>` header and an HTTP-only cookie `hospital_token`). The JWT signature is verified using a shared secret or public key defined in `HOSPITAL_JWT_SECRET` in `.env`.

### Rationale
- Decouples WCCMS entirely from credential storage and login forms.
- JWT verification is stateless and requires zero network overhead back to the hospital system for token validation.
- Supporting both Bearer header and HTTP-only cookie ensures compatibility with both direct web browser navigation and API/AJAX requests.

### Alternatives Considered
- **Local Login Form with Passwords**: Rejected — explicit feature requirement is external identity delegation; WCCMS must not collect or store passwords.
- **Reverse Proxy Header Ingestion (`X-User-Id`)**: Rejected in Clarification Q1 in favor of cryptographically verifiable JWTs.
- **OAuth2 / OIDC Authorization Code Flow**: Rejected as overly complex for a single shared infrastructure deployment.

---

## 2. First-Time User Auto-Provisioning & Approval State

### Decision
When a valid, verified JWT arrives for a username (`PER_USER_USERNAME`) that does not yet exist in `PER_USERS_TB`:
1. WCCMS auto-provisions a new record in `PER_USERS_TB` with a newly generated UUID primary key (`PER_USER_CODE`).
2. `PER_USER_ROLE` is set to `NULL` (unassigned).
3. `PER_USER_IS_ACTIVE` is set to `0` (inactive / pending approval).
4. `PER_USER_PASSWORD` is left unpopulated (`NULL`).
5. Access is denied with an Arabic pending-approval HTTP 403 response ("حسابك قيد المراجعة والاعتماد من قبل مسؤول النظام").

An `ADMIN` user must log into WCCMS to assign a role (`NURSE` or `ADMIN`), link an employee record (`PER_EMP_USER_CD`), and set `PER_USER_IS_ACTIVE = 1` before the user can log in.

### Rationale
- Prevents unauthorized hospital staff from accessing clinical wound care records immediately upon possessing a generic hospital JWT.
- Leaves role management strictly under WCCMS control, fulfilling the spec requirement that roles are WCCMS-local.
- Auto-provisioning ensures the username is captured automatically without requiring manual string entry by admins.

### Alternatives Considered
- **Auto-provisioning with default active `NURSE` role**: Rejected in Clarification Q2 to enforce explicit admin approval.
- **Blocking without auto-provisioning**: Rejected because manual account creation by admins would be error-prone when matching exact external usernames.

---

## 3. Unauthenticated Fallback & Redirect Behavior

### Decision
When a web browser request lacks a valid JWT or has an expired token:
- For standard web GET requests: Middleware redirects to `env('APP_HOSPITAL_LOGIN_URL')`.
- For JSON/AJAX requests (`Accept: application/json`): Middleware returns `HTTP 401 Unauthorized` with an Arabic JSON error payload (`{"message": "يرجى تسجيل الدخول من خلال بوابة المستشفى"}`).

### Rationale
- Provides seamless user experience for browser navigation while preserving standard RESTful API response contracts for background requests.
- Configurable URL via `.env` ensures flexibility across local development, staging, and production environments.

---

## 4. Local Session & Termination Strategy

### Decision
Once identity is resolved from a valid JWT, WCCMS initializes a standard local Laravel application session (`Auth::login($user)`). Local logout (navigating to `/logout`) flushes the WCCMS session data and clears local session cookies. WCCMS does NOT send single-logout (SLO) requests or back-channel notifications to the hospital system.

### Rationale
- Matches Clarification Q4 decision.
- Keeps local application lifecycle clean without introducing brittle external HTTP dependencies during logout.

---

## 5. Database Schema & Legacy Column Handling

### Decision
The existing MySQL table `per_users_tb` contains the column `PER_USER_PASSWORD`.
- **Database Schema**: Column is retained as-is without adding new migrations or dropping the column.
- **Eloquent Model**: `PER_USER_PASSWORD` is excluded from `$fillable` on `User.php`.
- **Application Logic**: WCCMS application logic never reads from or writes to `PER_USER_PASSWORD`.

### Rationale
- Matches Clarification Q5 decision.
- Ensures DB-First schema stability without unnecessary schema diffs.
