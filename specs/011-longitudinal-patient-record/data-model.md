# Data Model: Longitudinal Patient Medical Record

This feature introduces a derived read model only. It does not create tables, columns, or persisted state.

## Entity Graph

### Patient (`MrPatient`)

- **Identity**: Patient code/UUID.
- **Displayed attributes**: Existing demographic banner fields such as name, identity, gender, birth date, and phone where available.
- **Relationship**: Has many registrations.
- **Invariant**: Demographics render once at the patient level.

### Registration (`WcRegistration`)

- **Identity**: `WC_REG_CODE`.
- **Ownership**: Belongs to one patient.
- **Displayed attributes**: Status, registration date/time, department, responsible nurse, procedure, medical history, entry user, and discharge context.
- **Relationships**: Department, procedure, nurse, entry user, discharging nurse, diagnoses/wounds, daily reviews, and scheduled reviews.
- **Derived state**:
  - `isCurrent`: status is active.
  - `isInitiallyOpen`: first item after longitudinal ordering.
  - Summary counts for wounds, reviews, and appointments.
- **Ordering**: Active registrations first; then `WC_REG_DATE DESC`; then `WC_REG_CODE DESC`.
- **Invariant**: All content inside the rendered episode must resolve to this registration.

### Registration Diagnosis / Wound (`WcRegDiagnosis`)

- **Identity**: `WC_RDIAG_CODE`.
- **Ownership**: Belongs to one registration.
- **Displayed attributes**: Diagnosis, wound type, anatomical/clinical data already captured, clinical employee, and entry user.
- **Relationships**: Diagnosis lookup, wound type, clinical employee, entry user, daily review details, and wound photos.
- **Longitudinal projection**: Only initial-stage photos that are not owned by a review detail are rendered in the wound baseline area.
- **Ordering**: Stable `WC_RDIAG_CODE` order where no creation timestamp exists.
- **Invariant**: A baseline photo renders once for its wound.

### Daily Review (`WcDailyReview`)

- **Identity**: `WC_DREV_CODE`.
- **Ownership**: Belongs to one registration and may fulfill one scheduled review.
- **Displayed attributes**: Visit date/time, visit type, outcome, notes, clinical employee, and entry user.
- **Relationships**: Nurse/clinical employee, entry user, scheduled review, and review details.
- **Ordering**: `WC_DREV_VISIT_DATE DESC`, then `WC_DREV_CODE DESC`.
- **Invariant**: Reviews never move into a patient-wide mixed timeline; they remain within their registration.

### Daily Review Detail (`WcDailyReviewDetail`)

- **Identity**: `WC_REVD_CODE`.
- **Ownership**: Belongs to one daily review and one registered diagnosis/wound.
- **Displayed attributes**: Wound observations, dressing, medication, and outcome fields already stored.
- **Relationships**: Daily review, registered diagnosis, dressing, medication, and wound photos.
- **Ordering**: Stable `WC_REVD_CODE` order where no creation timestamp exists.
- **Invariant**: The linked diagnosis must belong to the same registration as the parent daily review before rendering.

### Scheduled Review (`WcScheduledReview`)

- **Identity**: `WC_SREV_CODE`.
- **Ownership**: Belongs to one registration.
- **Displayed attributes**: Scheduled date/time, status, notes, creator, and fulfilled-review link where available.
- **States**: Render the stored state, including pending, completed, missed, cancelled, or voided when present; this feature does not change transitions.
- **Ordering**: `WC_SREV_SCHEDULED_DATE DESC`, then `WC_SREV_CODE DESC`.

### Wound Photo (`WcWoundPhoto`)

- **Identity**: `WC_PHOTO_CODE`.
- **Ownership**: Belongs to one registered diagnosis and optionally one daily review detail.
- **Displayed attributes**: Authenticated image URL, stage, taken-at timestamp, clinical employee, and entry user where available.
- **Baseline projection**: Initial photo with no review-detail owner, rendered in the wound section.
- **Review projection**: Photo linked to the current review detail, rendered only in that review.
- **Ordering**: `WC_PHOTO_TAKEN_AT` with `WC_PHOTO_CODE` as deterministic tie-breaker.
- **Invariant**: One photo appears at most once in the longitudinal page.

### Clinical Employee and Entry User

- **Clinical employee**: The person responsible for the clinical act or observation.
- **Entry user**: The account that entered the information.
- **Invariant**: These identities remain separate even when they refer to the same person.

## Derived Longitudinal Read Model

```text
PatientRecord
├── patient
├── registrationCount
└── registrationEpisodes[]
    ├── registrationSummary
    ├── isInitiallyOpen
    ├── wounds[]
    │   └── baselinePhotos[]
    ├── appointments[]
    └── reviews[]
        └── woundAssessments[]
            └── reviewPhotos[]
```

The projection is assembled for the request and is not persisted. The controller eager-loads the graph; Blade formats it without issuing queries.

## Validation and Isolation Rules

1. Resolve the patient through existing route model binding and authorization middleware.
2. Retrieve registrations through the patient's relationship.
3. Retrieve every nested item through a registration-owned relationship.
4. Render a review detail only when its wound belongs to the same registration as the review.
5. Render a baseline photo only in the wound baseline collection.
6. Render a follow-up photo only through its owning review detail.
7. Preserve primary and foreign keys in any constrained select so Eloquent can match relations.

## Empty States

- Patient with no registrations: show one patient-level empty state.
- Registration with no wounds, appointments, or reviews: show a concise empty state in the applicable section.
- Missing optional employee, procedure, notes, or discharge data: show the established neutral placeholder without hiding the episode.

## State Transitions

The page is read-only. Registration and scheduled-review state transitions continue to be performed through existing focused workflows. The longitudinal page only reflects current stored states.
