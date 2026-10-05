# Interface Contract: Wound Photo Documentation API & Forms (Phase 10)

## 1. Registration Intake / Acceptance (Initial Wound Photos)

**Endpoint**: `POST /requests/{requestModel}/accept`

**Form Payload Extension**:
- `diagnoses[i][wound_photos][]`: File Array (Optional, Mimes: jpg, jpeg, png, webp, Max: 5MB per file).

**Processing**:
For each uploaded photo in `diagnoses[i][wound_photos]`:
- File is saved to `storage/app/public/wound_photos/{YYYY}/{MM}/{uuid}.{ext}`.
- Sibling record created in `wc_wound_photo_tb`:
  - `WC_PHOTO_CODE`: UUID
  - `WC_PHOTO_REG_DIAGNOSIS_CD`: `$regDiag->WC_RDIAG_CODE`
  - `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`: `null`
  - `WC_PHOTO_STAGE`: `'INITIAL'`
  - `WC_PHOTO_PATH`: `'wound_photos/{YYYY}/{MM}/{uuid}.{ext}'`
  - `WC_PHOTO_TAKEN_AT`: `now()`
  - `WC_PHOTO_NURSE_CD`: `$clinicNurseCode`
  - `WC_PHOTO_ENTRY_USER_CD`: `$userCode`

---

## 2. Daily Review Recording (Follow-up Review Wound Photos)

**Endpoint**: `POST /daily-reviews`

**Form Payload Extension**:
- `details[i][wound_photos][]`: File Array (Optional, Mimes: jpg, jpeg, png, webp, Max: 5MB per file).

**Processing**:
For each uploaded photo in `details[i][wound_photos]`:
- File is saved to `storage/app/public/wound_photos/{YYYY}/{MM}/{uuid}.{ext}`.
- Record created in `wc_wound_photo_tb`:
  - `WC_PHOTO_CODE`: UUID
  - `WC_PHOTO_REG_DIAGNOSIS_CD`: `$detail->WC_DREVD_REG_DIAGNOSIS_CD`
  - `WC_PHOTO_DAILY_REVIEW_DETAIL_CD`: `$detail->WC_REVD_CODE`
  - `WC_PHOTO_STAGE`: `'REVIEW'`
  - `WC_PHOTO_PATH`: `'wound_photos/{YYYY}/{MM}/{uuid}.{ext}'`
  - `WC_PHOTO_TAKEN_AT`: `now()`
  - `WC_PHOTO_NURSE_CD`: `$clinicNurseCode`
  - `WC_PHOTO_ENTRY_USER_CD`: `$userCode`

---

## 3. Photo Media Viewer Endpoint

**Endpoint**: `GET /wound-photos/{photo}`

**Response**: HTTP 200 Binary Stream with appropriate Content-Type header (`image/jpeg`, `image/png`, `image/webp`).
