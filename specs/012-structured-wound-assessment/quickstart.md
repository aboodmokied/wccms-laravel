# Quickstart: Validate Structured Wound Assessment

## Prerequisites

- PHP 8.3 and the repository dependencies are installed.
- The normal PHPUnit suite uses in-memory SQLite.
- Local procedure integration requires the configured MySQL `wccms` database.
- A hospital-authenticated administrator and nurse/clinical employee are available.
- Final assessment-choice stable meanings and Arabic labels have clinical-owner approval recorded in `contracts/hospital-form-field-matrix.md` before the choice seeder is created or run.

## Prepare the Application

```powershell
php artisan migrate --no-interaction
php artisan db:seed --class=WcAssessmentChoiceSeeder --no-interaction
npm run build
```

Expected result:

- Registration shared-context columns and new assessment tables exist.
- The clinical-choice catalog contains active category-qualified rows.
- MySQL workflow procedures are installed by the feature migration.
- Frontend assets compile without Vite errors.

## Automated Validation

Run the focused HTTP and domain tests:

```powershell
php artisan test --compact tests/Feature/StructuredInitialAssessmentTest.php
php artisan test --compact tests/Feature/StructuredDailyReviewTest.php
php artisan test --compact tests/Feature/StructuredScheduledReviewTest.php
php artisan test --compact tests/Feature/WoundAssessmentHistoryTest.php
php artisan test --compact tests/Feature/RbacAuthorizationTest.php
php artisan test --compact tests/Feature/AssessmentChoiceAdminTest.php
```

Run procedure integration tests against MySQL when the integration environment is configured:

```powershell
php artisan test --compact --group=mysql-clinical-procedures
```

Run regression coverage for the affected workflows:

```powershell
php artisan test --compact tests/Feature/ClinicIntakeAndRegistrationTest.php
php artisan test --compact tests/Feature/DailyReviewTest.php
php artisan test --compact tests/Feature/ScheduledReviewTest.php
php artisan test --compact tests/Feature/PatientMedicalRecordTest.php
php artisan test --compact tests/Feature/WoundPhotoDocumentationTest.php
```

Format modified PHP files:

```powershell
vendor/bin/pint --dirty --format agent
```

## Scenario 1: Complete Initial Assessment

1. Open a pending registration request.
2. Set allergies to “Yes” without entering a description and attempt acceptance.
3. Confirm the Arabic error identifies the shared allergy field and preserves all wound values.
4. Add an allergy description, select either PMH conditions or “None,” and create three diagnosis panels.
5. Enter distinct structured findings, measurements, treatment, and optional photos for each wound.
6. Leave one required field empty and confirm no registration is created.
7. Complete the field and accept the request.

Expected result:

- One registration and three registration diagnoses are created atomically.
- Each diagnosis has exactly one baseline assessment with its own findings and photos.
- Length/width reject zero; depth accepts zero; all dimensions reject values above 1000 or more than two decimals.
- Shared allergies, PMH, and follow-up plan appear once.
- The nurse, optional WCT/doctor participants, entry user, and timestamp remain distinguishable.

## Scenario 2: Daily Review for Every Wound

1. Open the daily-review form for the registration.
2. Verify all three wound panels appear and each shows only its latest comparable assessment.
3. Set one wound to dry and also select an exudate type.
4. Omit another wound’s assessment and submit.
5. Confirm Arabic errors identify both affected wounds without clearing the other panels.
6. Resolve the conflicts, enter distinct treatments and photos, and submit.
7. Repeat the same request with the same submission key.

Expected result:

- The valid submission creates one visit, three details, and three canonical follow-up assessments.
- Values and photos do not cross wound boundaries.
- Retrying the submission does not create a second visit.
- The authoritative outcome is read from the assessment's linked review detail and is not duplicated on the canonical assessment.

## Scenario 3: Scheduled Review Parity

1. Open a pending scheduled review, including one belonging to an older discharged registration.
2. Confirm the form matches the daily clinical dataset and contains every registration diagnosis.
3. Complete the visit and verify the appointment becomes completed and links to the visit.
4. Try to complete the same appointment again.
5. Try to submit a diagnosis from another registration.

Expected result:

- The first valid completion succeeds atomically.
- Repeat completion and cross-registration diagnosis input are rejected.
- Missed, cancelled, or voided appointments cannot accept assessments.

## Scenario 4: Longitudinal Comparison and Legacy Fallback

1. Open the registration record and then the patient’s longitudinal record.
2. Expand each wound across baseline, daily, and scheduled events.
3. Verify measurements use millimetres and all structured labels, treatments, photos, and audit identities appear under the correct wound.
4. Open a legacy registration whose review details predate structured assessments.

Expected result:

- Events are chronological and scoped to the owning wound and registration.
- Inactive historical choices retain their saved Arabic labels.
- Legacy narrative, dressing, medication, outcome, photos, and audit data remain visible.
- Missing structured fields display “لم يُسجل” and are never inferred.

## Scenario 5: Choice Lifecycle

1. As an administrator, deactivate a choice already used by an assessment.
2. Open a new assessment form and confirm the choice is unavailable.
3. Reopen the historical assessment and confirm its label is still visible.
4. Attempt to submit the inactive choice manually.

Expected result:

- New input rejects inactive or wrong-category choices.
- Historical display remains unchanged.

## Scenario 6: Photo Storage Failure Boundaries

1. Simulate storage failure for one validated baseline photo before request acceptance.
2. Confirm the clinical workflow gateway is not called and no registration or assessment is created.
3. Allow final-path storage, then simulate a procedure rejection after the files are written.
4. Confirm the failed aggregate creates no clinical rows and every newly written file is removed.
5. Repeat the same checks for a daily review.

Expected result:

- Successful photo metadata always points to a file that existed at its immutable final path before the procedure call.
- A storage failure produces no database mutation.
- A procedure failure leaves neither partial clinical rows nor newly written orphan files.
- No successful database commit requires a later file rename or move.

## Representative Clinical User Protocol

Use a representative cohort agreed by the clinical owner. Record anonymized evidence only; do not include patient or employee-identifying data.

For each participant:

1. Provide the same prepared patient context and three distinct wound scenarios.
2. Ask the participant to complete the initial registration without assistance; record completion, whether shared patient/encounter information was re-entered, assistance requested, and elapsed time.
3. Ask the participant to complete a follow-up for the three wounds; record completion, assistance requested, and elapsed time.
4. Ask the participant to locate a named wound's latest status, dimensions, treatment, and photo in the longitudinal record; record success, assistance requested, and elapsed seconds.
5. Record failure reasons by scenario so the interface can be corrected and the affected trials repeated.

Acceptance thresholds:

- SC-003 passes when at least 90% complete the three-wound initial registration without assistance or repeated patient/encounter entry.
- SC-004 passes when at least 90% complete the three-wound follow-up in under 8 minutes.
- SC-007 passes when at least 90% locate the requested wound facts in 30 seconds or less.
- Record the cohort definition, participant count, anonymized results, pass-rate calculations, and clinical-owner acknowledgement in `usability-evidence.md`.

## Acceptance Evidence

Capture the following for feature review:

- Focused and regression PHPUnit results.
- MySQL procedure integration result.
- Successful production asset build.
- Desktop and narrow-screen screenshots for the initial form, follow-up form, and wound history.
- Clinical-owner sign-off for the Arabic vocabulary in `contracts/hospital-form-field-matrix.md`.
- Anonymized representative-user results and calculated SC-003, SC-004, and SC-007 pass rates in `usability-evidence.md`.
