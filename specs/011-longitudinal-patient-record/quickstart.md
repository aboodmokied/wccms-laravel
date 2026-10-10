# Quickstart: Longitudinal Patient Medical Record

## Prerequisites

- Use the existing local application configuration and database.
- Ensure PHP 8.3 dependencies and frontend dependencies are installed.
- Use an authenticated hospital user with access to the patient and registration pages.

## Focused automated verification

Run the new feature test while implementing:

```powershell
php artisan test --compact tests/Feature/PatientMedicalRecordTest.php
```

Run the related existing registration, review, appointment, and wound-photo feature tests identified in the test suite:

```powershell
php artisan test --compact --filter="PatientMedicalRecord|RegistrationDetails|WoundPhoto|ScheduledReview"
```

If PHP files changed, format them:

```powershell
vendor/bin/pint --dirty --format agent
```

Build the page assets and validate compiled Blade templates:

```powershell
npm run build
php artisan view:cache
php artisan view:clear
```

Run the complete suite after focused checks pass:

```powershell
php artisan test --compact
```

## Required feature scenarios

The focused test should prove:

1. Active registrations appear before historical registrations; historical registrations are newest first.
2. When no registration is active, the newest historical registration is initially open.
3. Equal registration or review dates use their codes as stable tie-breakers.
4. A patient with one registration has no misleading historical-history empty state.
5. A patient with no registrations sees the patient-level empty state.
6. Reviews, appointments, wounds, and photos never leak across registrations or patients.
7. Reviews are newest first within their owning registration.
8. Scheduled and unscheduled visits and all stored appointment states render clearly.
9. Clinical employee and data-entry user remain distinct.
10. Each baseline photo renders once and each follow-up photo renders under its owning review.
11. Registration sections with no wounds, appointments, or reviews show useful empty states.
12. Every episode links to its dedicated registration page and historical sections expose no mutation controls.
13. Existing missing-token redirect behavior still protects the patient page.

## Manual UI acceptance

Use a representative patient with at least one active registration, two historical registrations, multiple wounds, appointments in different states, reviews, and photos.

Verify:

- Patient demographics appear once.
- The current episode is open and earlier episodes are collapsed in the expected order.
- Every collapsed summary explains the episode without opening it.
- Expanding multiple registrations does not produce duplicate control IDs or mix their data.
- Baseline photos remain in their wound section; review photos remain in the owning review.
- The page is usable with keyboard only and visible focus.
- Arabic labels, dates, identifiers, and status text remain readable in RTL on desktop and narrow mobile widths.
- Dark mode and reduced motion follow the application's existing behavior.
- Dedicated registration and authenticated image links work.

## Performance acceptance

Prepare representative fixtures containing approximately 10 registrations and 200 reviews. Compare database query logs for a small record and the representative record; relationship query count should remain effectively constant rather than growing per review or wound. Confirm that initial useful content renders in under two seconds in the target environment and that image responses are deferred until needed.

If the target is missed:

1. Capture response timing, HTML size, query count, and slow query plans.
2. Remove any accidental lazy loading or view-time query first.
3. Consider a measured follow-up using historical fragment loading or review pagination.
4. Add composite ordering indexes only when `EXPLAIN` demonstrates the need.
