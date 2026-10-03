# API & Controller Contracts: Phase 6 — Daily Review

## 1. Web Routes & Views

### `GET /registrations/{registration}/daily-reviews`
- **Controller**: `DailyReviewController@index`
- **Middleware**: `auth`, `rbac:NURSE,ADMIN`
- **Description**: Renders the main Daily Review workspace Blade view (`reviews.daily`), passing registration record with relations (`patient`, `department`, `procedure`, `nurse`, `dischargingNurse`, `diagnoses.woundType`, `diagnoses.diagnosis`, `diagnoses.dailyReviewDetails.dressing`, `diagnoses.dailyReviewDetails.medication`, `dailyReviews.details`, `dailyReviews.nurse`, `dailyReviews.scheduledReview`).
- **Response**: HTML View (`reviews.daily`)

---

## 2. Store Daily Review Action

### `POST /registrations/{registration}/daily-reviews`
- **Controller**: `DailyReviewController@store`
- **Middleware**: `auth`, `rbac:NURSE,ADMIN`
- **Request Validation (`StoreDailyReviewRequest`)**:

```json
{
  "nurse_code": "required|string|size:36|exists:per_employee_tb,PER_EMP_CODE",
  "overall_status": "required|string|in:IMPROVED_DISCHARGE,IMPROVED_SCHEDULED,WORSENED_STAY,DEATH",
  "general_notes": "nullable|string",
  "scheduled_date": "nullable|date",
  "details": "required|array|min:1",
  "details.*.diagnosis_code": "required|string|size:36|exists:wc_reg_diagnosis_tb,WC_RDIAG_CODE",
  "details.*.dressing_code": "nullable|string|size:36|exists:wc_dressing_tb,WC_DRESS_CODE",
  "details.*.medication_code": "nullable|string|size:36|exists:wc_medication_tb,WC_MED_CODE",
  "details.*.observation": "nullable|string",
  "details.*.outcome_status": "required|string|in:IMPROVED_DISCHARGE,IMPROVED_FOLLOWUP,WORSENED,DECEASED"
}
```

*Note: The Form Request automatically normalizes direct DB keys (`WC_DREV_NURSE_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREVD_REG_DIAGNOSIS_CD`, etc.) to the above structure if passed.*

- **Business Logic Execution**:
  1. Verify registration status is `ACTIVE`. If `DISCHARGED`, return `403 Forbidden` / error message.
  2. Verify registration has active wound diagnoses. If empty, return error message.
  3. Start `DB::transaction()`.
  4. Create `WcDailyReview` record with UUID PK `WC_DREV_CODE`, set `WC_DREV_ENTRY_USER_CD = auth()->user()->PER_USER_CODE`, `WC_DREV_VISIT_DATE = now()`.
  5. Create `WcDailyReviewDetail` records for each item in `details`.
  6. If `WC_DREV_OVERALL_STATUS` is `IMPROVED_DISCHARGE` or `IMPROVED_SCHEDULED`:
     - Set `WC_REG_STATUS = 'DISCHARGED'`, `WC_REG_DISCHARGED_AT = now()`, `WC_REG_DISCHARGED_BY_EMP_CD = $nurseCode`.
     - Save registration.
  7. If `WC_DREV_OVERALL_STATUS == 'IMPROVED_SCHEDULED'`:
     - Create `WcScheduledReview` record (`WC_SREV_CODE` = UUID, `WC_SREV_REGISTRATION_CD` = `$registration->WC_REG_CODE`, `WC_SREV_STATUS` = `'PENDING'`).
     - Link `WC_DREV_SCHEDULED_REVIEW_CD` to newly created appointment.
  8. Commit transaction.
- **Success Response**: Redirect back to `/registrations/{registration}/daily-reviews` with flash success message (`تم تسجيل التقييم والزيارة اليومية بنجاح`).
