# Quickstart & Validation Guide: Wound Photo Documentation (Phase 10)

## Prerequisites

1. MySQL `wccms` database configured and accessible.
2. Storage link enabled via `php artisan storage:link`.

---

## Runnable Verification Steps

### Step 1: Database Setup & Migration Check
Run migration/table creation for `wc_wound_photo_tb`:
```bash
php artisan migrate
```

### Step 2: Test Initial Baseline Photo Upload on Registration Acceptance
1. Log in to WCCMS as a Nurse user.
2. Navigate to incoming requests at `/requests`.
3. Open a pending request and click **Accept & Start Registration**.
4. Fill in initial diagnosis details and attach one or more wound photos.
5. Click **Submit Accept Request**.
6. Verify redirect to `/registrations` with success message.
7. Open registration details page at `/registrations/{id}`.
8. Verify initial wound photo renders under **Visual Progression Timeline** with badge `INITIAL`.

### Step 3: Test Review Photo Upload on Daily Review
1. Navigate to `/daily-reviews/create` for the registration.
2. Complete daily assessment details for the wound and attach a new review photo.
3. Submit daily review.
4. Open patient registration details page.
5. Verify both Initial Photo and Review Photo display in chronological order with side-by-side comparison mode enabled.

### Step 4: Automated Verification Test Runner
Run automated PHPUnit feature tests for wound photo documentation:
```bash
php artisan test --compact --filter=WoundPhotoDocumentationTest
```
