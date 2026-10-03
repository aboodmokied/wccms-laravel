# Quickstart Verification Guide: Phase 6 — Daily Review

## 1. Environment Setup & Prerequisites

Ensure the local environment is running and seeded with Phase 1 reference data and active registrations from Phase 4/5.

```bash
# Verify database connection and seeders
php artisan db:seed --class=WccmsMasterDataSeeder

# Run local development server
php artisan serve
```

---

## 2. Automated Feature Tests Execution

Run the narrow test suite covering Daily Review features:

```bash
# Run all daily review tests
php artisan test --filter=DailyReview
```

Expected test cases:
1. `test_diagnosis_can_be_created_without_dressing_and_medication()`
2. `test_dressing_and_medication_are_stored_in_daily_review_detail_tb()`
3. `test_overall_status_is_stored_in_daily_review_tb()`
4. `test_per_wound_improved_discharge_does_not_discharge_registration()`
5. `test_overall_improved_discharge_discharges_registration()`
6. `test_overall_improved_scheduled_discharges_registration_and_creates_scheduled_review()`
7. `test_overall_worsened_stay_does_not_discharge_registration()`
8. `test_overall_death_follows_header_workflow_without_wound_confusion()`
9. `test_daily_review_stores_attending_nurse_and_auth_entry_user()`
10. `test_discharged_registration_blocks_new_daily_reviews()`

---

## 3. Manual Verification Steps

1. **Login & Access Registration**:
   - Access `http://127.0.0.1:8000/mock-hospital-auth` and log in as a nurse.
   - Navigate to Registered Patients (`/registrations`).
   - Click an `ACTIVE` registration to open its Daily Review page (`/registrations/{registration}/daily-reviews`).

2. **Inspect Workspace**:
   - Verify patient summary card renders demographics (National ID, Gender, Department, Nurse).
   - Verify active wound tabs display wound diagnosis and current dressing/medication derived from latest daily review detail entries.

3. **Submit Daily Review**:
   - Click **"+ إضافة مراجعة يومية (Add Daily Review)"**.
   - Complete observation, dressing, medication, and per-wound outcome for each active wound.
   - Select overall visit outcome: **"تحسن – خروج ومراجعة مجدولة" (`IMPROVED_SCHEDULED`)**.
   - Select attending nurse and click **"حفظ المراجعة اليومية"**.

4. **Verify Outcome & Lock**:
   - Confirm success toast message renders in Arabic.
   - Verify registration status badge changes to **`DISCHARGED`**.
   - Verify "+ Add Daily Review" button is hidden/disabled.
   - Verify the review timeline card renders today's review notes and wound detail outcomes.
