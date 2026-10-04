# Phase 1: Quickstart Validation Guide — Master Data Administration

## Feature Branch: `008-master-data-admin`

---

## 1. Prerequisites & Setup

Ensure local migrations and seeders are loaded:
```bash
# Refresh/seed database if needed
php artisan db:seed --class=MasterDataSeeder

# Run narrow test suite for baseline sanity
php artisan test --filter=MasterDataAdminTest
```

---

## 2. Validation Scenarios

### Scenario 1: RBAC Access Protection
**Goal**: Verify that non-admin users cannot access master data endpoints.

1. Log in as a `NURSE` user.
2. Attempt to navigate to `/admin/departments` or `/admin/medications`.
3. Assert that access is denied with HTTP 403 Forbidden or authorization error redirect.
4. Log in as an `ADMIN` user and verify `/admin/departments` opens successfully.

---

### Scenario 2: Creating and Editing Reference Items
**Goal**: Verify that an Admin can add a new item and edit its name.

1. On `/admin/medications`, click **"إضافة دواء جديد"**.
2. Enter a new unique medication name (e.g. `"مرهم الفضية المتقدم"`) and submit.
3. Assert the item appears in the list with status **نشط** (Active).
4. Click **"تعديل"**, change the name to `"مرهم الفضية المطور"`, and submit.
5. Assert name updates successfully in database and UI.

---

### Scenario 3: Activation Toggling (No Hard Deletes)
**Goal**: Verify that toggling status updates `IS_ACTIVE` without deleting records.

1. On `/admin/dressings`, click **"تعطيل"** for an active dressing.
2. Assert status badge updates to **غير نشط** (Inactive).
3. Open a clinical form (e.g. Daily Review modal at `/registrations/{id}/daily-reviews`), verify the deactivated dressing is excluded from selection.
4. Return to `/admin/dressings` and click **"تفعيل"**.
5. Assert status badge updates back to **نشط** (Active) and re-appears in form dropdowns.

---

## 3. Automated Test Execution Commands

```bash
# Run narrow feature test suite
php artisan test --filter=MasterDataAdminTest

# Run code style formatting check
vendor/bin/pint --dirty --format agent
```
