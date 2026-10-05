# Phase 0 Research: Wound Photo Documentation (Phase 10)

## Technical Decisions & Rationale

### Decision 1: Database Storage Model vs Binary BLOB
- **Decision**: Store photo metadata and relative file paths in `wc_wound_photo_tb` table (`WC_PHOTO_PATH`), storing binary image files on disk under `storage/app/public/wound_photos/{year}/{month}/`.
- **Rationale**: Storing files on disk keeps MySQL/Oracle database backups lightweight and query performance optimal, while utilizing standard web server static file serving or authenticated media controllers.
- **Alternatives Considered**: Database BLOB storage was rejected due to database bloat and memory overhead during query execution.

---

### Decision 2: Foreign Key Architecture for Initial vs Review Photos
- **Decision**: `WC_PHOTO_REG_DIAGNOSIS_CD` is mandatory (`CHAR(36)` FK to `wc_reg_diagnosis_tb`). `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` is nullable (`CHAR(36)` FK to `wc_daily_review_detail_tb`).
- **Rationale**: Every wound photo belongs to a specific wound diagnosis (`WcRegDiagnosis`). Baseline initial photos (`INITIAL`) have `WC_PHOTO_DAILY_REVIEW_DETAIL_CD = NULL`. Review photos (`REVIEW`) populate both foreign keys, associating the photo with both the wound diagnosis and the specific daily review detail item.
- **Alternatives Considered**: Creating separate tables for initial photos vs daily review photos was rejected to maintain a single unified photo model and timeline query.

---

### Decision 3: Image Validation & File Format Standardization
- **Decision**: Accept `jpeg`, `jpg`, `png`, `webp` formats up to 5MB (5120 KB). Generate unique UUID filenames (`{uuid}.{extension}`) upon upload.
- **Rationale**: Supports camera uploads directly from mobile devices/tablets as well as desktop file pickers. UUID file naming avoids file collisions and path traversal risks.
- **Alternatives Considered**: Raw uncompressed BMP or TIFF formats were rejected due to storage constraints.

---

### Decision 4: Multiple Photos per Wound per Visit
- **Decision**: Support array file uploads `diagnoses.*.wound_photos.*` and `details.*.wound_photos.*`.
- **Rationale**: Clinicians need the flexibility to document complex, deep, or multi-faceted wounds from multiple angles (e.g. top view, side depth view) during a single visit.
- **Alternatives Considered**: Restricting uploads to exactly 1 photo per visit was rejected per user clarification.
