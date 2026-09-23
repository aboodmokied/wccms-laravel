# Quickstart & Verification Guide: Phase 3 — Auth & RBAC

**Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md)

This guide documents automated and manual verification procedures to validate Phase 3 Auth & RBAC functionality.

---

## 1. Automated Test Execution

Run the narrowest feature test suite for Phase 3 via Artisan:

```bash
php artisan test --filter=HospitalJwtAuthMiddlewareTest --compact
php artisan test --filter=RbacAuthorizationTest --compact
php artisan test --filter=UserManagementTest --compact
```

Or run all Phase 3 tests together:

```bash
vendor/bin/phpunit --filter="HospitalJwtAuthMiddlewareTest|RbacAuthorizationTest|UserManagementTest"
```

---

## 2. End-to-End Validation Scenarios

### Scenario 1: Unauthenticated Navigation Redirect
1. **Action**: Open browser and navigate to `http://localhost/dashboard` without a hospital JWT.
2. **Expected Outcome**: Browser redirects immediately to `APP_HOSPITAL_LOGIN_URL` (HTTP 302).

### Scenario 2: First-Time Hospital User Auto-Provisioning & Pending State
1. **Action**: Send HTTP request with valid JWT for a new hospital username `new_nurse_01`.
2. **Expected Outcome**:
   - `per_users_tb` contains a new row with `PER_USER_USERNAME = 'new_nurse_01'`, `PER_USER_ROLE = NULL`, `PER_USER_IS_ACTIVE = 0`.
   - User receives HTTP 403 with Arabic message: `"حسابك قيد المراجعة والاعتماد من قبل مسؤول النظام"`.

### Scenario 3: Admin Activation & Role Assignment
1. **Action**: Admin accesses user management (`/admin/users`), assigns `NURSE` role to `new_nurse_01`, links employee `EMP-001`, and sets status to Active (`1`).
2. **Expected Outcome**:
   - `PER_USER_ROLE` updated to `'NURSE'`.
   - `PER_USER_IS_ACTIVE` updated to `1`.
   - `PER_EMP_USER_CD` set to `PER_USER_CODE`.

### Scenario 4: Authenticated Nurse Access & Admin Restriction
1. **Action**: User `new_nurse_01` accesses `/dashboard` with valid JWT token.
2. **Expected Outcome**:
   - Dashboard renders within 1.5 seconds.
   - Top navbar header displays employee full name (`PER_EMP_FULL_NAME`).
3. **Action**: User `new_nurse_01` attempts to navigate to `/admin/users`.
4. **Expected Outcome**: Access denied with HTTP 403 and Arabic message: `"غير مصرح لك بالوصول إلى هذه الصفحة"`.

### Scenario 5: User Account Deactivation
1. **Action**: Admin updates `new_nurse_01` status to Inactive (`0`).
2. **Expected Outcome**:
   - Next request by `new_nurse_01` is blocked with HTTP 403.
   - Rendered Arabic message states: `"تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول"`.
