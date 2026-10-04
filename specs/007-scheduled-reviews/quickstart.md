# Phase 1: Quickstart Validation Guide — Scheduled Reviews & Appointments Management

## Feature Branch: `007-scheduled-reviews`

---

## 1. Prerequisites & Setup

Ensure the application environment and database are configured:
```bash
# Ensure local database migrations and seeders are updated
php artisan migrate

# Run PHPUnit test suite for existing core features to verify baseline sanity
php artisan test --compact
```

---

## 2. Validation Scenarios

### Scenario 1: Scheduled Reviews Queue Listing & Filtering
**Goal**: Verify that `/reviews/scheduled` displays tabbed counters (Today, Upcoming, Overdue, Completed, Cancelled) and correctly filters appointments by search and date range.

1. Navigate to `/reviews/scheduled` in the browser.
2. Verify summary ribbon displays:
   - **اليوم (Today)** badge count
   - **القادمة (Upcoming)** badge count
   - **المتأخرة (Overdue)** badge count
   - **المكتملة (Completed)** badge count
   - **الملغاة (Cancelled)** badge count
3. Test searching by patient name or ID number in the search input.
4. Verify list filters dynamically and maintains Arabic RTL styling.

---

### Scenario 2: Fulfilling a Scheduled Appointment via Daily Review Visit
**Goal**: Verify that starting a visit from a pending scheduled review pre-fills the daily review form and transitions the appointment to `COMPLETED`.

1. On `/reviews/scheduled`, locate a `PENDING` appointment under Today, Upcoming, or Overdue.
2. Click **"بدء زيارة الموعد"** (Start Scheduled Visit).
3. Confirm system redirects to `/daily-reviews/create` with `scheduled_review` code attached.
4. Fill in wound evaluation details and click **"حفظ المراجعة اليومية"** (Save Daily Review).
5. Verify:
   - Daily review is saved successfully.
   - `wc_scheduled_review_tb` record transitions `WC_SREV_STATUS` to `COMPLETED` and sets `WC_SREV_COMPLETED_ON`.
   - `wc_daily_review_tb` record sets `WC_DREV_SCHEDULED_REVIEW_CD` matching the appointment UUID.

---

### Scenario 3: Rescheduling & Manual Cancellation
**Goal**: Verify that staff can reschedule an appointment date or manually cancel an appointment with notes.

1. Locate a `PENDING` appointment on `/reviews/scheduled`.
2. Click **"إعادة جدولة"** (Reschedule), pick a new date/time, and save.
   - Assert `WC_SREV_SCHEDULED_DATE` is updated and status remains `PENDING`.
3. Click **"إلغاء الموعد"** (Cancel Appointment), enter cancellation reason in notes modal, and submit.
   - Assert status transitions to `CANCELLED` and cancellation notes are appended.

---

### Scenario 4: Hard Deletion of Old Pending Appointments on Re-Registration
**Goal**: Verify that accepting a new registration for a patient automatically hard-deletes all `PENDING` scheduled reviews from their previous registrations.

1. Create a patient with an existing registration that has 2 `PENDING` scheduled reviews.
2. Submit and accept a **new** registration request for the same patient.
3. Assert in database/tests that `wc_scheduled_review_tb` no longer contains the old `PENDING` appointments for that patient (`DELETE` statement executed).

---

## 3. Automated Test Verification Command

Run the dedicated Scheduled Reviews feature test suite:
```bash
# Run narrow feature test suite
php artisan test --filter=ScheduledReviewTest

# Run code style formatter check
vendor/bin/pint --dirty --format agent
```
