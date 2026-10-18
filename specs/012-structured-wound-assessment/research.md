# Research: Structured Wound Assessment & Hospital Form Parity

## Decision 1: Use one canonical wound-assessment record for baseline and follow-up states

**Decision**: Add `WC_WOUND_ASSESSMENT_TB`. Each row belongs to exactly one registration diagnosis and represents either an `INITIAL` baseline or a `FOLLOW_UP` assessment. Follow-up assessments optionally link one-to-one to the existing `WC_REVIEW_DETAIL_TB` row that places them inside a daily or scheduled clinical visit.

**Rationale**: Initial and follow-up forms share most wound-state fields. A canonical assessment record prevents two incompatible clinical schemas while preserving the existing visit header/detail hierarchy and its longitudinal history behavior.

**Alternatives considered**:

- Add structured columns directly to `WC_REG_DIAGNOSIS_TB` and `WC_REVIEW_DETAIL_TB`: rejected because field definitions would be duplicated and comparisons would require two schemas.
- Replace `WC_REVIEW_DETAIL_TB`: rejected because legacy narrative, outcome, dressing, medication, and photo relationships must remain readable.
- Store the structured assessment as JSON: rejected because field ownership, validation, indexing, stable choices, and later Oracle porting require explicit relational columns.

## Decision 2: Preserve legacy review details as compatibility snapshots

**Decision**: New follow-up writes create the existing review-detail row and a linked canonical wound-assessment row in one database transaction. Existing narrative, wound-change, dressing, medication, outcome, and follow-up fields remain populated on the detail row. `WC_REVIEW_DETAIL_TB.WC_DREVD_STATUS` is the single authoritative outcome value for both new and legacy follow-ups; the canonical assessment reaches it through its required unique review-detail relationship and does not duplicate an outcome column. Legacy rows without a linked assessment render their original data and show structured values as “لم يُسجل”.

**Rationale**: This provides a non-destructive migration path and keeps existing reports, timelines, photos, and tests meaningful while the structured assessment becomes the source for new comparisons.

**Alternatives considered**:

- Backfill structured values from narrative text: rejected because the specification prohibits clinical inference.
- Leave compatibility fields blank for new records: rejected because existing history and reporting paths would regress during incremental rollout.

## Decision 3: Use a stable clinical-choice catalog with category-qualified references

**Decision**: Add `WC_ASSESSMENT_CHOICE_TB` with stable keys, Arabic labels, category, display order, and active state. Scalar assessment selections reference catalog rows. Multi-select wound-bed and wound-margin findings use `WC_ASSESSMENT_FINDING_TB`. Pocketing is seeded only under `WOUND_MARGIN`.

**Rationale**: Stable identifiers preserve historical meaning when labels change or choices are retired. Category checks prevent a choice from being submitted in the wrong clinical field. Existing dressing, medication, procedure, diagnosis, wound type, and PMH masters remain authoritative for their domains.

**Alternatives considered**:

- Database enum or hard-coded strings: rejected because choices must be deactivatable and remain readable historically.
- Separate master table for every category: rejected because it creates repetitive models, controllers, and administration screens for small vocabularies with the same lifecycle.

## Decision 4: Store allergies, comorbidities, and follow-up planning once per registration

**Decision**: Extend `WC_REGISTRATION_TB` with allergy presence/description, no-comorbidities flag, and shared follow-up date/frequency/objective. Add `WC_REGISTRATION_PMH_TB` for selected existing PMH rows. The “None” state is represented by the registration flag and must be mutually exclusive with PMH links.

**Rationale**: These facts apply to the encounter, not one wound. Registration ownership avoids repeated input and conflicting copies across diagnosis panels.

**Alternatives considered**:

- Repeat shared values on each assessment: rejected because it permits contradictions and violates the clarified workflow.
- Add “None” to the PMH master: rejected because absence of comorbidity is registration state rather than a medical-history condition.

## Decision 5: Keep clinical mutations behind database procedure interfaces

**Decision**: Introduce a Laravel clinical-workflow gateway with MySQL stored-procedure implementations for request acceptance with complete baselines and for daily/scheduled visit completion. Procedures validate registration ownership, complete diagnosis coverage, choice category/activity, measurement bounds, dry/exudate consistency, appointment state, audit identities, and idempotency before committing related rows.

**Rationale**: The project constitution requires registration creation and daily/scheduled review completion to remain database-owned core workflows. A narrow gateway keeps controllers focused on HTTP validation and supports mocked procedure-interface tests.

**Alternatives considered**:

- Continue multi-model Eloquent transactions in controllers: rejected because it bypasses the constitution’s database-first rule for core clinical mutations.
- One procedure call per individual row: rejected because partial registration or visit records could survive an intermediate failure.

## Decision 6: Pass validated structured payloads as JSON to MySQL procedures

**Decision**: Laravel validates the request shape, serializes the registration/visit payload and diagnosis assessments to JSON, and calls a workflow procedure once. MySQL procedures use JSON functions to validate and insert the complete aggregate. The Phase 9 Oracle port will rewrite the procedure contract in PL/SQL while retaining the same logical payload and result contract.

**Rationale**: A variable number of diagnoses, multi-select findings, and treatment references cannot be represented cleanly by a fixed scalar procedure signature. The constitution explicitly treats MySQL procedures as executable specifications to be rewritten during the Oracle phase.

**Alternatives considered**:

- Temporary staging tables keyed by session: rejected because cleanup, concurrency, and retry behavior are more complex.
- Controller-side row loops around procedure calls: rejected because aggregate completeness and atomicity would be harder to guarantee.

## Decision 7: Protect visit completion with an idempotency key and database uniqueness

**Decision**: Add a required unique submission key to new clinical visit headers and a unique constraint on non-null scheduled-review linkage. The form receives a UUID submission key. Repeating the same submission returns the first successful visit; a second completion attempt for the same scheduled appointment is rejected without changing the first record.

**Rationale**: This handles browser retries and double submission at the database boundary and directly supports FR-026.

**Alternatives considered**:

- Disable the submit button only: rejected because it does not cover retries, multiple tabs, or concurrent requests.
- One daily visit per calendar day: rejected because it is broader than duplicate protection and could prevent a clinically valid additional unscheduled encounter.

## Decision 8: Use decimal millimetre measurements with explicit validation

**Decision**: Store length, width, and depth as `DECIMAL(8,2)`. Length and width are `(0, 1000]`; depth is `[0, 1000]`. Values are presented with `mm`. Procedures repeat the domain checks after request validation.

**Rationale**: Decimal storage preserves exact entered measurements, supports the clarified two-decimal limit, and avoids floating-point comparison artifacts.

**Alternatives considered**:

- Integer millimetres: rejected because the clarified workflow permits two decimals.
- Floating point: rejected because exact clinical values and predictable validation are preferable.

## Decision 9: Reuse one follow-up form and one presentation component

**Decision**: Daily and scheduled reviews continue using the same Blade form and request contract. Each diagnosis panel shows its identity, most recent comparable assessment, structured inputs, treatment, narrative fields, and photos. Registration and patient history reuse one assessment-display partial.

**Rationale**: A shared surface enforces daily/scheduled parity, reduces clinical training cost, and avoids divergence between two follow-up data sets.

**Alternatives considered**:

- Separate daily and scheduled forms: rejected because parity would depend on maintaining duplicate templates.

## Decision 10: Verify through HTTP behavior plus procedure-contract coverage

**Decision**: PHPUnit feature tests cover Arabic validation, ownership, complete diagnosis coverage, scheduled state, duplicate submission, stored audit identities, history rendering, retired choices, and legacy fallback. SQLite tests bind a deterministic test implementation of the clinical-workflow gateway; a MySQL integration group verifies real procedure behavior against the local database schema.

**Rationale**: The main suite remains fast and portable while the database-first boundary receives explicit integration coverage, satisfying the constitution’s real/mocked procedure-interface gate.

**Alternatives considered**:

- Test only controllers with mocked responses: rejected because relational ownership and atomicity defects would be missed.
- Require MySQL for every test: rejected because it would slow the normal suite and conflict with the established in-memory SQLite configuration.

## Decision 11: Persist photos to immutable final paths before clinical commit

**Decision**: After request validation, Laravel assigns each photo its UUID-based final relative path and writes the file to that path before calling the clinical workflow gateway. The aggregate payload contains only successfully persisted paths and metadata. A storage failure aborts before any procedure call. If the procedure rejects or rolls back the aggregate, Laravel removes the newly written files. No successful database commit is followed by a required rename or move.

**Rationale**: Database and filesystem writes cannot share one transaction. Pre-persisting an immutable path ensures committed photo metadata already points to an existing file and eliminates the failure mode where the database succeeds but final file placement fails. A process interruption before the procedure can leave an unreferenced file, which is safer than a clinical record pointing to a missing file and can be removed by an age-bounded orphan cleanup operation.

**Alternatives considered**:

- Commit photo metadata first and finalize staged files afterward: rejected because a failed post-commit move leaves a readable clinical record pointing to a missing file.
- Store binary image data in the database: rejected because it conflicts with the established public-disk photo design and expands database scope unnecessarily.
