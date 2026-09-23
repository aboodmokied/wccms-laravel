# Quickstart Validation Guide: Phase 4 — Registration Requests

**Feature**: [`spec.md`](spec.md) | **Branch**: `004-registration-requests`

## Validation Overview

This guide provides runnable instructions to verify end-to-end functionality for Phase 4 Registration Requests across database procedures, feature tests, and Blade web interfaces.

---

## 1. Automated Verification (PHPUnit & Database Procedures)

Run feature tests specifically covering registration requests and database procedures:

```bash
# Run narrow test suite for registration requests
php artisan test --filter=RegistrationRequestTest

# Run procedure integration test for sp_accept_registration_request
php artisan test --filter=AcceptRegistrationRequestProcedureTest
```

### Key Automated Test Cases Covered

1. **`test_ward_nurse_can_search_and_submit_referral_request()`**: Validates patient lookup, snapshot generation, and `PENDING` request creation.
2. **`test_duplicate_pending_request_is_blocked()`**: Confirms attempt to create duplicate request for patient with active request or registration fails validation.
3. **`test_clinic_nurse_can_start_ward_visit()`**: Confirms state transition `PENDING` → `VISIT_IN_PROGRESS`.
4. **`test_accepting_request_invokes_database_procedure_and_creates_registration()`**: Confirms calling accept transitions request to `ACCEPTED` and inserts `ACTIVE` record in `wc_registration_tb` with default department.
5. **`test_submitting_nurse_can_cancel_pending_request()`**: Confirms transition `PENDING` → `CANCELLED`.

---

## 2. Manual Web Interface Verification

### Environment Setup

```bash
# Ensure local development database is migrated & seeded
php artisan migrate:fresh --seed

# Start development server
php artisan serve
```

### Verification Scenarios

1. **Ward Referral Submission**:
   - Navigate to `/requests/create`.
   - Enter National ID `401234567` and click **بحث** (Search).
   - Verify patient demographics and hospital ward info display.
   - Enter referral notes and submit.
   - Verify redirect to `/requests/my` showing new request badge `قيد الانتظار`.

2. **Clinic Queue & Ward Visit Intake**:
   - Navigate to `/requests` (Incoming Requests Queue).
   - Click on the new pending request to view details at `/requests/{id}`.
   - Click **بدء الزيارة** (Start Visit) and confirm status changes to `زيارة قيد التنفيذ`.
   - Click **قبول وتنسيق التسجيل** (Accept & Register).
   - Verify redirect to `/registrations` with new active registration record listed.

3. **Arabic RTL Layout Compliance**:
   - Check page orientation, font alignment, form controls, and status badges across all `/requests/*` routes.
   - Verify zero English fallback string errors or broken assets.
