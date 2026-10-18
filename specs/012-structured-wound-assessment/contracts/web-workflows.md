# Web Workflow Contract

## Shared Contract Rules

- All routes retain the existing hospital authentication middleware and role rules.
- HTML forms use CSRF protection and return Arabic validation messages keyed to the affected wound panel.
- UUID route parameters and reference values are 36-character strings.
- Uploaded photos remain JPEG, JPG, PNG, or WebP with a maximum size of 5 MiB per file.
- After validation, each photo is written to its immutable final UUID-based relative path before the clinical procedure is called. Storage failure prevents the procedure call; procedure rejection removes the newly written files. No required file move occurs after a successful database commit.
- Clinical workflow controllers validate shape and files, then pass one aggregate payload to the database procedure gateway.
- A validation failure redirects back with input and wound-qualified errors. No clinical row is committed.

## Initial Registration Assessment

### Display

`GET /requests/{requestModel}` (`requests.show`)

For an eligible request, the response supplies:

- patient and encounter context;
- active departments, wound types, diagnoses, dressings, medications, procedures, PMH rows, clinical employees, and assessment choices grouped by category;
- one diagnosis panel by default and the ability to add more panels client-side;
- shared allergy, comorbidity, and follow-up-plan controls.

### Complete acceptance

`POST /requests/{requestModel}/accept` (`requests.accept`)

Shared payload:

| Field | Shape | Requirement |
|---|---|---|
| `department_code` | UUID | Required active department. |
| `clinic_nurse_code` | UUID | Required clinical nurse. |
| `wct_employee_code` | UUID/null | Optional active WCT participant. |
| `doctor_employee_code` | UUID/null | Optional active doctor participant. |
| `has_allergies` | boolean | Required. |
| `allergies_description` | string/null | Required when `has_allergies = 1`; otherwise empty. |
| `no_comorbidities` | boolean | Required. |
| `pmh_codes[]` | unique UUID array | Active PMH rows; forbidden when `no_comorbidities = 1`. |
| `pmh_notes` | string/null | Maximum 2000 characters; preserves existing narrative history. |
| `next_visit_date` | date/null | Today or later; at least one of date/frequency is required. |
| `follow_up_frequency` | string/null | Maximum 100 characters; at least one of date/frequency is required. |
| `follow_up_objective` | string | Required when a date or frequency is supplied. |
| `diagnoses[]` | nonempty array | Every element is one complete initial wound assessment. |

Each `diagnoses[n]` contains:

| Field | Shape | Requirement |
|---|---|---|
| `wound_type_code` | UUID | Required active wound type. |
| `diagnosis_code` | UUID | Required active diagnosis. |
| `wound_status_choice_code` | UUID | Required `WOUND_STATUS` choice; initial selection is `FIRST_VISIT`. |
| `wound_bed_choice_codes[]` | unique UUID array | One or more active `WOUND_BED` choices. |
| `wound_margin_choice_codes[]` | unique UUID array | One or more active `WOUND_MARGIN` choices; `NONE_OBSERVED` is exclusive. |
| `exudate_amount_choice_code` | UUID | Required active `EXUDATE_AMOUNT` choice. |
| `exudate_type_choice_code` | UUID/null | Null for `DRY`; required otherwise. |
| `odor_choice_code` | UUID | Required active `ODOR` choice. |
| `pain_severity_choice_code` | UUID | Required active `PAIN_SEVERITY` choice. |
| `pain_at_dressing` | boolean | Required. |
| `fever_choice_code` | UUID | Required active `FEVER` choice. |
| `length_mm` | decimal | Required, `> 0` and `<= 1000`, at most two decimals. |
| `width_mm` | decimal | Required, `> 0` and `<= 1000`, at most two decimals. |
| `depth_mm` | decimal | Required, `>= 0` and `<= 1000`, at most two decimals. |
| `location_choice_code` | UUID | Required active `LOCATION` choice. |
| `laterality_choice_code` | UUID/null | Category `LATERALITY`; required where clinically applicable. |
| `location_notes` | string/null | Maximum 500; required when location is `OTHER`. |
| `swab_taken` | boolean | Required. |
| `nutrition_choice_code` | UUID | Required active `NUTRITION` choice. |
| `dressing_code` | UUID/null | Active dressing. |
| `medication_code` | UUID/null | Active medication. |
| `procedure_code` | UUID/null | Active procedure; debridement is submitted here. |
| `treatment_notes` | string/null | Additional plan; one treatment value is required. |
| `wound_photos[]` | files/null | Optional baseline photos for this diagnosis only. |

Successful outcome:

- The request becomes accepted.
- One registration, its PMH links, all diagnosis rows, complete initial assessments/findings, photo metadata pointing to already persisted final-path files, and optional scheduled appointment exist.
- Response redirects to the created registration or existing post-acceptance destination with an Arabic success message.

Failure outcomes:

- `422`/redirect errors for incomplete fields, invalid measurements, dry/exudate conflict, inactive/wrong-category choices, duplicate selections, allergy/PMH conflict, or past follow-up date.
- `409`/Arabic error for a request already decided or a repeated completed acceptance.
- The database procedure commits no partial registration.
- A storage failure calls no clinical procedure, and a procedure failure removes every file prewritten for that failed aggregate.

## Add a Diagnosis to an Active Registration

`POST /registrations/{registration}/diagnoses` (`registrations.diagnoses.store`)

This workflow accepts the same complete per-diagnosis initial-assessment fields documented under `diagnoses[n]`, plus the required clinical nurse and optional WCT/doctor participants. It does not accept a minimal diagnosis-only row. The database workflow atomically creates the registration diagnosis, its baseline assessment/findings, and optional baseline photos only when the registration is active and every reference is valid. The shared allergy, PMH, and follow-up context remains registration-owned and is not duplicated.

## Daily Follow-Up

### Display

`GET /reviews/daily/{registration}/create` (`reviews.daily.create`)

The response supplies one panel for every registration diagnosis. Each panel includes its latest canonical assessment or a legacy summary when no canonical assessment exists. A discharged registration cannot start an unscheduled daily review.

### Complete

`POST /reviews/daily/{registration}` (`reviews.daily.store`)

Header payload:

| Field | Shape | Requirement |
|---|---|---|
| `submission_key` | UUID | Required idempotency token. |
| `nurse_code` | UUID | Required clinical actor. |
| `wct_employee_code` | UUID/null | Optional WCT participant. |
| `doctor_employee_code` | UUID/null | Optional doctor participant. |
| `overall_status` | existing allowed status | Required. |
| `general_notes` | string/null | Shared visit notes. |
| `next_visit_date` | date/null | Shared plan; today or later. |
| `follow_up_frequency` | string/null | Shared plan frequency. |
| `follow_up_objective` | string/null | Required if date/frequency is supplied. |
| `details[]` | array | Exactly one complete item for every diagnosis in the registration. |

Each `details[n]` keeps the diagnosis and wound type read-only. It requires wound status (excluding `FIRST_VISIT`), wound-bed findings, exudate, odor, pain, and treatment. Wound-margin findings and measurements may be recorded when assessed; initial-only fever, nutrition, swab, and anatomical-location fields are not repeated. It additionally contains:

| Field | Shape | Requirement |
|---|---|---|
| `registration_diagnosis_code` | UUID | Required; unique in payload and owned by route registration. |
| `clinical_observation` | string/null | Follow-up narrative. |
| `wound_changes` | string/null | Change since previous assessment. |
| `outcome_status` | existing detail outcome | Required. Stored once as the authoritative value in `WC_REVIEW_DETAIL_TB.WC_DREVD_STATUS`; the linked canonical assessment does not duplicate it. |
| `follow_up_required` | boolean | Required. |
| `wound_photos[]` | files/null | Optional follow-up photos for this diagnosis only. |

Successful outcome creates one visit header, one legacy-compatible detail and one canonical assessment per diagnosis, all findings and photo metadata pointing to already persisted final-path files, and any shared next appointment. Replaying the same `submission_key` returns the original success without creating a second visit. A storage failure calls no procedure; a procedure failure removes the files prewritten for that failed aggregate.

## Scheduled Follow-Up

### Display

`GET /reviews/scheduled/{scheduledReview}/create` (`reviews.scheduled.create`)

Only a `PENDING` appointment may render the form. It uses the same choice sets, latest-assessment data, and diagnosis panels as the daily form, including appointments belonging to an older discharged registration.

### Complete

`POST /reviews/scheduled/{scheduledReview}` (`reviews.scheduled.store`)

The payload is identical to daily completion. The route supplies the scheduled-review identity; clients cannot substitute another appointment or registration. Success creates one linked clinical visit and changes the appointment to `COMPLETED`. Completed, missed, cancelled, or voided appointments return an Arabic conflict error and create no assessment.

## History Display

### Registration record

`GET /registrations/{registration}` (`registrations.show`)

For each wound, show the baseline and follow-ups chronologically, including measurements, structured choices, treatment, the authoritative `WC_DREVD_STATUS` outcome from the linked review detail, photos, clinical actor, optional participants, entry user, and timestamp. Legacy rows continue to show narrative fields and label unavailable structured values as `لم يُسجل`.

### Longitudinal patient record

`GET /patients/{patient}` (`patients.show`)

The same assessment presentation is nested under its owning registration and wound. Queries must scope all diagnoses, assessments, findings, reviews, and photos through the requested patient.

## Assessment Choice Administration

Authorized administrators may list, rename, order, activate, and deactivate `WC_ASSESSMENT_CHOICE_TB` rows. Stable category/key values are immutable after creation. Deactivation removes the choice from new entry forms and retains its historical Arabic label in existing assessments.
