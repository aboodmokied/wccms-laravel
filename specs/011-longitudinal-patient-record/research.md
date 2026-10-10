# Research: Longitudinal Patient Medical Record

## Decision 1: Preserve the patient and registration route roles

**Decision**: Keep `GET /patients/{patient}` as the authenticated read-only longitudinal record and keep `GET /registrations/{registration}` as the focused registration workflow. Add no route for this phase.

**Rationale**: The existing routes already express the two user intents. The patient page can aggregate episodes without duplicating edit actions, while every episode can link to its dedicated page.

**Alternatives considered**:

- Redirect all history use to registration pages. This prevents viewing prior episodes in patient context.
- Add a new longitudinal route. This duplicates the meaning of the current patient route without improving authorization or navigation.

## Decision 2: Use server-rendered progressive disclosure

**Decision**: Render the complete bounded history in the initial HTML, keep only the selected registration open, and use native `<details>/<summary>` for registrations and reviews. Images use browser lazy loading.

**Rationale**: The specified scale of 10 registrations and 200 reviews is suitable for constrained eager loading. Native disclosure is keyboard accessible, remains useful without JavaScript, keeps browser history simple, and avoids fragment loading error and focus states.

**Alternatives considered**:

- Load historical registration fragments on demand and paginate reviews. This better bounds HTML at much larger scales, but adds a route, JavaScript state, authorization checks, pagination interaction, and partial-load failure states before measurements show they are needed.
- Keep the horizontal slider. It obscures long histories and is harder to scan or navigate by keyboard.
- Use current/history tabs. Tabs hide episode boundaries and make older registrations harder to compare.

## Decision 3: Constrain eager loading by ownership

**Decision**: Load the full read graph in the controller with registration-scoped Eloquent relationships. Load initial wound photos through the diagnosis baseline collection and follow-up photos only through their owning review detail.

**Rationale**: This prevents N+1 queries, preserves registration ownership, and ensures a photo has one rendered owner. It also keeps clinical employees separate from data-entry users.

**Alternatives considered**:

- Query relationships from Blade. Existing photo presentation code demonstrates that this can cause N+1 work and duplicate page-global IDs.
- Load every diagnosis photo and also every detail photo. The overlapping graph can render the same photo more than once.
- Cache the assembled clinical record. Invalidation adds stale-data risk while the measured scale does not require it.

## Decision 4: Apply deterministic portable ordering

**Decision**: Order active registrations before historical registrations, then registration date descending and registration code descending. Order reviews and appointments by their dates descending with their codes descending as tie-breakers. Order wounds/details/photos by stable codes or recorded timestamps plus codes where available.

**Rationale**: Date ties occur in real data. Stable code tie-breakers produce repeatable UI and tests, and use constructs compatible with the project's MySQL-first and Oracle-later direction.

**Alternatives considered**:

- Date-only sorting. Equal dates can reorder between requests.
- Database-vendor-specific ranking functions. They are unnecessary at this scale and weaken portability.

## Decision 5: Reuse and parameterize the review timeline

**Decision**: Keep one review timeline partial and add inputs for unique ID prefixes, initial disclosure, baseline-photo behavior, and action visibility. The patient page hides entry actions and assigns a registration-specific prefix.

**Rationale**: One rendering contract prevents the registration and patient pages from drifting. Parameters resolve the current duplicate IDs and repeated baseline behavior when multiple timelines share a page.

**Alternatives considered**:

- Copy review markup into the patient page. Duplication would increase maintenance and could produce inconsistent clinical fields.
- Reuse the existing wound-photo timeline unchanged. It performs view-time queries and uses page-global modal and accordion IDs.

## Decision 6: Keep Bootstrap RTL and introduce page-scoped CSS

**Decision**: Use the current Bootstrap 5 RTL components and clinical styles, create `resources/css/pages/patients.css` for the outer history presentation, and register it with Vite. Use no patient-specific JavaScript unless implementation evidence identifies a native-disclosure gap.

**Rationale**: This follows the live UI convention, removes inline styling and slider code, and gives the page responsive, dark-mode, reduced-motion, and focus behavior through existing conventions.

**Alternatives considered**:

- Introduce Tailwind utilities on this page. Tailwind is installed, but mixing it into the current Bootstrap page would create two styling systems within one feature.
- Keep inline CSS and pointer-drag JavaScript. It is harder to maintain and unnecessary with native disclosure.

## Decision 7: Make no schema change before measurement

**Decision**: Use the existing foreign-key and primary-key indexes. Measure representative queries and rendered response time before proposing composite ordering indexes.

**Rationale**: The schema already indexes patient-to-registration and each nested ownership relation. At 10–20 registrations and 200 reviews, application-level N+1 removal and lazy image downloads are the first performance controls.

**Alternatives considered**:

- Add composite indexes preemptively. This adds write and migration cost without measured evidence.
- Add a denormalized longitudinal table. It duplicates clinical truth and conflicts with the read-only aggregation requirement.

## Decision 8: Verify behavior with focused feature tests and representative timing

**Decision**: Add `PatientMedicalRecordTest` using existing direct-model setup and JWT helpers. Cover ordering, ownership isolation, stable ties, identities, appointments, photo placement, empty states, and bounded query growth. Run related existing feature tests and a Vite build.

**Rationale**: These checks target the risks introduced by aggregation. A growth comparison is more durable than an exact query-count assertion, and wall-clock success remains an environment-level acceptance check.

**Alternatives considered**:

- Assert one exact global query count. Minor framework or eager-load changes would make the test brittle.
- Depend only on browser inspection. It cannot reliably detect cross-registration leakage or query growth.
