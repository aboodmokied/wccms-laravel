# Feature Specification: Wound Photo Documentation (Phase 10)

**Feature Branch**: `010-wound-photo-documentation`

**Created**: 2026-10-05

**Status**: Approved

## Clarifications

### Session 2026-10-05
- Q: هل التقاط/رفع صورة الجرح إجباري أم اختياري عند القبول والمراجعات؟ → A: اختياري (Optional) — يُسمح بإتمام القبول والتوثيق بدون صورة عند عدم توفرها لمنع تعطيل إجراءات القبول.
- Q: هل يُسمح برفع عدة صور للجرح الواحد في الزيارة الواحدة؟ → A: صور متعددة (Multiple Photos) — يُسمح برفع أكثر من صورة للجرح الواحد في الزيارة/المراجعة الواحدة لتوثيقه من زوايا مختلفة.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Initial Wound Photo Baseline Documentation (Priority: P1)

As a clinic nurse or attending clinician, when accepting a patient registration request or recording an initial wound diagnosis, I want to capture/upload a baseline photo of each wound so that a clear visual baseline of the wound's initial condition (dimensions, color, depth, and surrounding tissue) is permanently recorded in the patient's medical file.

**Why this priority**: Establishing an initial visual baseline at first diagnosis is critical for clinical comparison during subsequent visits.

**Independent Test**: Accept a registration request with wound diagnosis, upload an initial wound image, and verify the photo record is created with `WC_PHOTO_STAGE = 'INITIAL'` linked to the specific wound diagnosis.

**Acceptance Scenarios**:

1. **Given** a nurse accepting a registration request or recording an initial wound diagnosis, **When** submitting the diagnosis form with an optional wound photo file or camera capture, **Then** the system validates the image (JPEG/PNG/WebP, max 5MB), saves it securely to storage, and creates a `WC_WOUND_PHOTO_TB` record marked as `WC_PHOTO_STAGE = 'INITIAL'`.
2. **Given** an initial wound diagnosis submission without a photo, **When** submitting the form, **Then** the diagnosis is saved successfully without error, allowing photos to be added or updated later.

---

### User Story 2 - Follow-Up Wound Photo Capture During Daily Reviews (Priority: P1)

As a clinic nurse performing a daily review or follow-up assessment for a patient, I want to upload or capture a new photo for each assessed wound so that visual progress and healing outcomes can be tracked chronologically for every clinic visit.

**Why this priority**: Regular photo documentation during follow-up reviews provides objective visual evidence of wound healing progress, deterioration, or treatment response over time.

**Independent Test**: Perform a daily review on a patient registration, upload a photo for a wound assessment detail, and verify the photo record is saved with `WC_PHOTO_STAGE = 'REVIEW'` linked to both the wound diagnosis and the daily review detail item.

**Acceptance Scenarios**:

1. **Given** a nurse filling out a daily review form for a patient with one or more wounds, **When** attaching a photo for a specific wound assessment item, **Then** the system saves the image and creates a `WC_WOUND_PHOTO_TB` record linked to the `WC_DAILY_REVIEW_DETAIL_TB` record with `WC_PHOTO_STAGE = 'REVIEW'`.
2. **Given** multiple wounds evaluated in a single daily review visit, **When** photos are uploaded for some or all wounds, **Then** each photo is stored independently and linked to its corresponding wound detail.

---

### User Story 3 - Interactive Visual Progression Timeline & Gallery (Priority: P2)

As a physician, nurse, or medical administrator, I want to view an interactive chronological photo timeline and comparison gallery on the patient's registration page so that I can compare the baseline initial photo side-by-side with follow-up photos across all visits to assess treatment efficacy.

**Why this priority**: Side-by-side visual comparison gives clinicians immediate insights into healing rates, tissue repair, or complications.

**Independent Test**: Navigate to a patient's registration details page (`/registrations/{id}`), open the Wound Photo Progression tab/gallery, and verify all photos are ordered chronologically with initial baseline highlight and side-by-side comparison view.

**Acceptance Scenarios**:

1. **Given** a patient registration with initial and review wound photos, **When** viewing the registration details page, **Then** the system renders a chronological photo timeline gallery showing date, stage (`INITIAL` vs `REVIEW`), attending nurse, and optional clinical notes.
2. **Given** a clinician comparing wound progress, **When** selecting any follow-up photo, **Then** the gallery allows side-by-side comparison with the initial baseline photo of the same wound.
3. **Given** a photo thumbnail in the gallery, **When** clicking the image, **Then** an interactive lightbox modal opens displaying full-resolution image with zoom and details.

---

### Edge Cases

- **File upload errors / invalid formats**: If a user uploads a non-image file or a file exceeding 5MB, the system MUST show an Arabic validation error message and prevent form submission.
- **Wounds without photos**: Wounds without photos MUST be represented in the visual timeline with a clear "No photo recorded for this visit" placeholder without breaking layout rendering.
- **Multiple photos per visit**: If multiple photos are uploaded for a single wound in one visit, the system MUST store each photo with distinct timestamps and sequence indices.
- **Storage missing / permission errors**: If file storage fails, the database transaction MUST roll back completely, ensuring no orphan photo records exist without actual storage files.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST support uploading/capturing wound photos during initial diagnosis registration (`RegistrationRequestController@accept` and `RegistrationController@storeDiagnosis`).
- **FR-002**: System MUST support uploading/capturing wound photos during daily review assessments (`DailyReviewController@store`).
- **FR-003**: System MUST store photo files securely in `storage/app/public/wound_photos/{year}/{month}/` with unique UUID filenames.
- **FR-004**: System MUST record photo metadata in `WC_WOUND_PHOTO_TB` with fields: `WC_PHOTO_CODE` (PK), `WC_PHOTO_REG_DIAGNOSIS_CD` (FK), `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` (Nullable FK), `WC_PHOTO_STAGE` (`INITIAL`/`REVIEW`), `WC_PHOTO_PATH`, `WC_PHOTO_CAPTION`, `WC_PHOTO_TAKEN_AT`, `WC_PHOTO_NURSE_CD`, and `WC_PHOTO_ENTRY_USER_CD`.
- **FR-005**: System MUST validate image files (allowed types: `jpeg`, `jpg`, `png`, `webp`; max size: 5120 KB).
- **FR-006**: System MUST render a visual wound progression timeline & gallery component in patient registration view (`registrations/show.blade.php`).
- **FR-007**: System MUST provide side-by-side image comparison mode in the gallery (Initial Baseline vs Selected Follow-up Review).
- **FR-008**: System MUST enforce dual audit tracking recording both the examining nurse (`WC_PHOTO_NURSE_CD`) and data-entry user (`WC_PHOTO_ENTRY_USER_CD`).

### Key Entities *(include if feature involves data)*

- **Wound Photo Metadata (`WcWoundPhoto`)**: Sits in `wc_wound_photo_tb`, storing photo stage (`INITIAL`/`REVIEW`), file storage path, caption, timestamp, examining nurse, data-entry user, and foreign keys to registration diagnosis and daily review detail.
- **Visual Progression Gallery**: Front-end component rendering chronological photo progression per wound diagnosis with lightbox viewer and side-by-side baseline comparison.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Image upload and thumbnail generation completed in under 1 second per image.
- **SC-002**: 100% data integrity: every photo record in `wc_wound_photo_tb` maps to an existing file in disk storage and vice versa.
- **SC-003**: Registration details page with photo progression timeline loads in under 500ms.
- **SC-004**: Zero runtime errors or broken image links when displaying missing or corrupted photo references.
- **SC-005**: Photo comparison lightbox renders smoothly across modern desktop and mobile browsers in RTL layout.

## Assumptions

- Photos are uploaded via standard HTTP multipart form uploads or mobile camera inputs.
- Image storage uses Laravel's `public` disk configured via `php artisan storage:link`.
- Database schema follows the project's DB-First architecture rules (`CHAR(36)` UUID primary keys, `$timestamps = false`, `<PREFIX>_` column naming).
