# Data Model: Structured Wound Assessment & Hospital Form Parity

## Design Rules

- All persisted clinical identifiers are UUID strings stored as `CHAR(36)`.
- New database objects follow the established lowercase migration names and uppercase prefixed column names; Oracle migration work will preserve the logical names.
- Existing clinical records are never rewritten to infer structured values.
- New initial and follow-up assessments are immutable completed clinical facts. A correction workflow is outside this phase.
- Every new assessment records the clinical employee, authenticated entry user, and completion timestamp separately.

## Relationship Overview

```text
MR_PATIENT_TB
  └─ WC_REGISTRATION_TB
       ├─ WC_REGISTRATION_PMH_TB ── WC_PMH_TB
       ├─ WC_SCHEDULED_REVIEW_TB
       ├─ WC_DAILY_REVIEW_TB
       │    └─ WC_REVIEW_DETAIL_TB
       └─ WC_REG_DIAGNOSIS_TB
            ├─ WC_WOUND_ASSESSMENT_TB ── optional WC_REVIEW_DETAIL_TB
            │    └─ WC_ASSESSMENT_FINDING_TB ── WC_ASSESSMENT_CHOICE_TB
            └─ WC_WOUND_PHOTO_TB ── optional WC_WOUND_ASSESSMENT_TB
```

## Existing Entity Extensions

### `WC_REGISTRATION_TB`

Shared encounter values are stored once here.

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_REG_HAS_ALLERGIES` | boolean/small integer | Yes for legacy | New registrations require `0` or `1`. |
| `WC_REG_ALLERGIES_DESCRIPTION` | text | Yes | Required and nonblank when allergies are `1`; must be null when allergies are `0`. |
| `WC_REG_NO_COMORBIDITIES` | boolean/small integer | Yes for legacy | New registrations require `0` or `1`; `1` forbids PMH links. |
| `WC_REG_NEXT_VISIT_DATE` | datetime | Yes | Must not be earlier than the clinical completion date. |
| `WC_REG_FOLLOW_UP_FREQUENCY` | varchar(100) | Yes | Used when follow-up is expressed as a frequency. |
| `WC_REG_FOLLOW_UP_OBJECTIVE` | text | Yes | Required when either next date or frequency is supplied. |

Validation invariants:

- New accepted registrations must specify allergy and comorbidity states.
- At least one of next-visit date or follow-up frequency is required for the completed initial workflow unless the registration is immediately discharged by an existing workflow outcome.
- One follow-up objective is shared by all wounds in the registration.
- When `WC_REG_NEXT_VISIT_DATE` is present, acceptance creates one pending scheduled review for the registration.

### `WC_DAILY_REVIEW_TB`

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_DREV_SUBMISSION_KEY` | char(36) | Yes for legacy | Required and unique for every new daily or scheduled completion. |

Additional index:

- Unique index on `WC_DREV_SUBMISSION_KEY`.
- Unique index on nullable `WC_DREV_SCHEDULED_REVIEW_CD` so a scheduled appointment can produce at most one visit while legacy/non-scheduled visits may remain null.

### `WC_WOUND_PHOTO_TB`

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_PHOTO_ASSESSMENT_CD` | char(36) FK | Yes | New photos reference the owning assessment; legacy photos may remain null. |

New initial photos use `WC_PHOTO_STAGE = INITIAL`; new follow-up photos use `REVIEW`. Follow-up photos retain `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` for compatibility and also reference the canonical assessment. Before a clinical procedure is called, each validated upload is written to its immutable final UUID-based relative path. The procedure receives metadata only for files already present at those paths. Storage failure prevents the procedure call, and procedure failure causes application cleanup of the newly written unreferenced files; no post-commit rename is required.

## New Entities

### `WC_REGISTRATION_PMH_TB`

Associates a registration with zero or more existing PMH master rows.

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_RPMH_CODE` | char(36) PK | No | UUID. |
| `WC_RPMH_REGISTRATION_CD` | char(36) FK | No | References `WC_REGISTRATION_TB`. |
| `WC_RPMH_PMH_CD` | char(36) FK | No | References `WC_PMH_TB`. |

Constraints:

- Unique (`WC_RPMH_REGISTRATION_CD`, `WC_RPMH_PMH_CD`).
- New writes may use active PMH rows only.
- Historical links remain readable when a PMH row is inactive.
- No rows may exist when the owning registration has `WC_REG_NO_COMORBIDITIES = 1`.

### `WC_ASSESSMENT_CHOICE_TB`

Stores stable clinical meanings and Arabic display labels.

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_ACH_CODE` | char(36) PK | No | UUID. |
| `WC_ACH_CATEGORY` | varchar(40) | No | Category key listed below. |
| `WC_ACH_KEY` | varchar(50) | No | Stable machine meaning within the category. |
| `WC_ACH_LABEL_AR` | varchar(150) | No | Approved Arabic display label. |
| `WC_ACH_IS_ACTIVE` | small integer | No | `1` available for new input; `0` history only. |
| `WC_ACH_DISPLAY_ORDER` | integer | No | Deterministic form ordering. |

Constraints and indexes:

- Unique (`WC_ACH_CATEGORY`, `WC_ACH_KEY`).
- Index (`WC_ACH_CATEGORY`, `WC_ACH_IS_ACTIVE`, `WC_ACH_DISPLAY_ORDER`).
- Rows cannot be hard-deleted after use.

Initial categories and stable keys:

| Category | Stable keys |
|---|---|
| `WOUND_STATUS` | `FIRST_VISIT`, `DETERIORATING`, `STATIC`, `IMPROVING` |
| `WOUND_BED` | `GRANULATION`, `NECROTIC`, `SLOUGHING`, `EPITHELIALIZING` |
| `WOUND_MARGIN` | `NONE_OBSERVED`, `ERYTHEMA`, `EDEMA`, `DELAYED_HEALING`, `POCKETING` |
| `EXUDATE_AMOUNT` | `DRY`, `LOW`, `MEDIUM`, `HIGH` |
| `EXUDATE_TYPE` | `WATERY`, `CLOUDY`, `PURULENT`, `CLEAR` |
| `ODOR` | `NONE`, `SLIGHT`, `MODERATE`, `OFFENSIVE` |
| `PAIN_SEVERITY` | `NONE`, `MODERATE`, `SEVERE` |
| `FEVER` | `NONE`, `LOW`, `HIGH` |
| `NUTRITION` | `WELL_NOURISHED`, `MALNOURISHED` |
| `LOCATION` | `HEAD`, `ABDOMEN`, `BACK`, `LEG`, `BUTTOCKS`, `FOOT`, `HAND`, `FOREARM`, `OTHER` |
| `LATERALITY` | `NOT_APPLICABLE`, `LEFT`, `RIGHT`, `BILATERAL` |

The clinical owner must approve the Arabic labels before implementation acceptance. Pocketing belongs only to `WOUND_MARGIN`.

### `WC_WOUND_ASSESSMENT_TB`

Canonical diagnosis-specific clinical state at one point in time.

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_WA_CODE` | char(36) PK | No | UUID. |
| `WC_WA_REG_DIAGNOSIS_CD` | char(36) FK | No | Owning wound/diagnosis. |
| `WC_WA_REVIEW_DETAIL_CD` | char(36) FK | Yes | Null for baseline; unique for a linked follow-up detail. |
| `WC_WA_TYPE` | varchar(20) | No | `INITIAL` or `FOLLOW_UP`. |
| `WC_WA_CLINICAL_EMP_CD` | char(36) FK | No | Nurse responsible for the assessment. |
| `WC_WA_WCT_EMP_CD` | char(36) FK | Yes | Optional Wound Care Team participant. |
| `WC_WA_DOCTOR_EMP_CD` | char(36) FK | Yes | Optional doctor participant. |
| `WC_WA_ENTRY_USER_CD` | char(36) FK | No | Authenticated data-entry user. |
| `WC_WA_ASSESSED_AT` | datetime | No | Completion/attestation time. |
| `WC_WA_WOUND_STATUS_CD` | char(36) FK | No | Choice category `WOUND_STATUS`. |
| `WC_WA_EXUDATE_AMOUNT_CD` | char(36) FK | No | Choice category `EXUDATE_AMOUNT`. |
| `WC_WA_EXUDATE_TYPE_CD` | char(36) FK | Yes | Category `EXUDATE_TYPE`; null when amount is `DRY`. |
| `WC_WA_ODOR_CD` | char(36) FK | No | Choice category `ODOR`. |
| `WC_WA_PAIN_SEVERITY_CD` | char(36) FK | No | Choice category `PAIN_SEVERITY`. |
| `WC_WA_PAIN_AT_DRESSING` | boolean/small integer | No | Independent pain-at-dressing indicator. |
| `WC_WA_FEVER_CD` | char(36) FK | Yes for follow-up | Category `FEVER`; required for initial assessments. |
| `WC_WA_LENGTH_MM` | decimal(8,2) | Yes for follow-up | Required initially; greater than `0`, at most `1000` when recorded. |
| `WC_WA_WIDTH_MM` | decimal(8,2) | Yes for follow-up | Required initially; greater than `0`, at most `1000` when recorded. |
| `WC_WA_DEPTH_MM` | decimal(8,2) | Yes for follow-up | Required initially; from `0` through `1000` when recorded. |
| `WC_WA_LOCATION_CD` | char(36) FK | Yes for follow-up | Category `LOCATION`; required for initial assessments. |
| `WC_WA_LATERALITY_CD` | char(36) FK | Yes | Category `LATERALITY`; required when applicable. |
| `WC_WA_LOCATION_NOTES` | varchar(500) | Yes | Required when location is `OTHER`; optional precision otherwise. |
| `WC_WA_SWAB_TAKEN` | boolean/small integer | Yes for follow-up | Required initially; records collection only, not laboratory processing. |
| `WC_WA_NUTRITION_CD` | char(36) FK | Yes for follow-up | Category `NUTRITION`; required for initial assessments. |
| `WC_WA_DRESSING_CD` | char(36) FK | Yes | Existing dressing master. |
| `WC_WA_MEDICATION_CD` | char(36) FK | Yes | Existing medication master. |
| `WC_WA_PROCEDURE_CD` | char(36) FK | Yes | Existing procedure master; debridement is represented here. |
| `WC_WA_TREATMENT_NOTES` | text | Yes | Additional treatment plan. |
| `WC_WA_CLINICAL_OBSERVATION` | text | Yes | Follow-up narrative; retained alongside legacy detail snapshot. |
| `WC_WA_WOUND_CHANGES` | text | Yes | Change since the latest assessment. |
| `WC_WA_FOLLOW_UP_REQUIRED` | boolean/small integer | No | Diagnosis finding feeding the shared plan. |

Constraints and indexes:

- Unique `WC_WA_REVIEW_DETAIL_CD` when non-null.
- Index (`WC_WA_REG_DIAGNOSIS_CD`, `WC_WA_ASSESSED_AT`) for chronological history and latest-assessment lookup.
- `INITIAL` requires a null review detail; `FOLLOW_UP` requires a review detail.
- The procedure allows exactly one initial assessment per registration diagnosis.
- Referenced diagnosis, review detail, and review header must resolve to the same registration.
- New writes accept only active choice/master rows in the correct category; historical rows retain inactive references.
- At least one treatment value among dressing, medication, procedure, or treatment notes is required for an initial assessment. Follow-up treatment requirements follow the hospital-approved form rule.

### `WC_ASSESSMENT_FINDING_TB`

Multi-select wound-bed and wound-margin observations.

| Column | Type | Null | Rule |
|---|---|---:|---|
| `WC_AF_CODE` | char(36) PK | No | UUID. |
| `WC_AF_ASSESSMENT_CD` | char(36) FK | No | Owning canonical assessment. |
| `WC_AF_CHOICE_CD` | char(36) FK | No | Active `WOUND_BED` or `WOUND_MARGIN` choice for new records. |

Constraints:

- Unique (`WC_AF_ASSESSMENT_CD`, `WC_AF_CHOICE_CD`).
- Every completed assessment requires at least one `WOUND_BED` finding.
- Every initial assessment requires at least one `WOUND_MARGIN` selection. `NONE_OBSERVED` explicitly represents no listed abnormal margin finding and is mutually exclusive with other margin choices.
- Follow-up margin findings are optional; when entered, `NONE_OBSERVED` remains mutually exclusive.

## Existing Compatibility Entities

### `WC_REVIEW_DETAIL_TB`

New follow-up creation continues to populate:

- owning review and registration diagnosis;
- dressing and medication;
- wound description/clinical observation;
- wound changes;
- authoritative outcome status in `WC_DREVD_STATUS`;
- follow-up required.

The linked `WC_WOUND_ASSESSMENT_TB` row supplies the complete structured wound state. `WC_DREVD_STATUS` remains the single source of truth for the follow-up outcome and is exposed through the assessment's unique review-detail relationship; no outcome column is added to `WC_WOUND_ASSESSMENT_TB`. Legacy detail rows without an assessment remain valid and display “لم يُسجل” for structured fields.

### `WC_SCHEDULED_REVIEW_TB`

- One registration-level follow-up plan creates one pending scheduled row.
- A completed scheduled row links to exactly one daily-review visit through the unique visit foreign key.
- Only `PENDING` may transition to `COMPLETED` through assessment completion.
- `MISSED`, `CANCELLED`, or voided-equivalent states reject clinical completion.

## Aggregate Validation

### Initial Registration Acceptance

The database workflow accepts the request only when:

1. The request is eligible for acceptance.
2. Shared allergy, PMH, and follow-up values are internally consistent.
3. At least one diagnosis assessment is supplied.
4. Every supplied diagnosis has all required structured fields and at least one wound-bed finding.
5. Every reference is active, belongs to its expected category/domain, and every measurement is valid.
6. Every submitted photo file already exists at its immutable final path, and registration, registration-diagnosis, baseline assessment, findings, PMH links, optional scheduled appointment, photo metadata, and audit identities can be committed atomically. A procedure failure leaves no database rows and triggers cleanup of the prewritten files.

### Daily or Scheduled Visit Completion

The database workflow completes the visit only when:

1. The registration and optional scheduled appointment match.
2. The appointment is pending when supplied.
3. The submission key has not completed a different visit.
4. Submitted diagnosis codes are unique and equal the full set of diagnoses for the registration.
5. Each diagnosis has a complete structured assessment.
6. Dry exudate has no exudate type; non-dry exudate has an allowed type.
7. Every submitted photo file already exists at its immutable final path, and header, details, assessments, findings, photo metadata, appointment state, and any resulting shared follow-up appointment are committed atomically. A procedure failure triggers cleanup of the prewritten files.

## Lifecycle Summary

```text
Assessment choice: ACTIVE → INACTIVE
Registration diagnosis: active for every review → ends only when registration is DISCHARGED
Initial assessment: created atomically with registration → immutable
Daily visit: submitted once → completed immutable record
Scheduled appointment: PENDING → COMPLETED | MISSED | CANCELLED
Legacy detail: readable with no structured assessment → never inferred or rewritten
```
