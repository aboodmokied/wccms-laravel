# Contract Specification: Scheduled Reviews API & Web Endpoints

## Feature Branch: `007-scheduled-reviews`

---

## 1. Web & API Endpoint Summary

| Method | Endpoint | Controller Action | Description |
|---|---|---|---|
| `GET` | `/reviews/scheduled` | `ScheduledReviewController@index` | Main dashboard listing scheduled reviews with filters & stats |
| `POST` | `/reviews/scheduled/{scheduledReview}/fulfill` | `ScheduledReviewController@startFulfillment` | Redirects to daily review create form with appointment context |
| `PUT` | `/reviews/scheduled/{scheduledReview}/reschedule` | `ScheduledReviewController@reschedule` | Updates scheduled appointment date |
| `POST` | `/reviews/scheduled/{scheduledReview}/cancel` | `ScheduledReviewController@cancel` | Cancels a pending appointment |
| `GET` | `/patients/{patient}/scheduled-reviews` | `ScheduledReviewController@patientQueue` | View scheduled reviews for a specific patient |

---

## 2. Endpoint Specifications

### 2.1 GET `/reviews/scheduled`
Returns the Scheduled Reviews queue view with summary counters and paginated list.

#### Query Parameters:
- `tab` (optional, default `TODAY`): Filter tab (`TODAY`, `UPCOMING`, `OVERDUE`, `COMPLETED`, `CANCELLED`, `ALL`).
- `search` (optional): Filter by patient full name, patient hospital ID, or national ID.
- `date_from` (optional): Filter appointments starting from date (YYYY-MM-DD).
- `date_to` (optional): Filter appointments up to date (YYYY-MM-DD).
- `page` (optional): Page number for pagination.

#### View Response Data (`reviews/scheduled.blade.php`):
- `counts`: Array of summary numbers:
  - `today_count`: Pending appointments scheduled for today (`WC_SREV_SCHEDULED_DATE` = today).
  - `upcoming_count`: Pending appointments scheduled for future dates (`WC_SREV_SCHEDULED_DATE` > today).
  - `overdue_count`: Pending appointments scheduled for past dates (`WC_SREV_SCHEDULED_DATE` < today).
  - `completed_count`: Completed appointments (`WC_SREV_STATUS` = 'COMPLETED').
  - `cancelled_count`: Cancelled appointments (`WC_SREV_STATUS` = 'CANCELLED').
- `scheduledReviews`: LengthAwarePaginator of `WcScheduledReview` records with relations `['registration.patient', 'dailyReview']`.

---

### 2.2 POST `/reviews/scheduled/{scheduledReview}/fulfill`
Initiates fulfillment of a `PENDING` appointment.

#### Request Parameters:
- `{scheduledReview}`: Route parameter (UUID `WC_SREV_CODE`).

#### Behavioral Flow:
1. Verifies `$scheduledReview->WC_SREV_STATUS === 'PENDING'`.
2. If invalid status, redirects back with flash error: `"لا يمكن بدء مراجعة لموعد غير قائم (تم إنجازه أو إلغاؤه)"`.
3. If valid, redirects to `route('daily-reviews.create', ['registration' => $scheduledReview->WC_SREV_REGISTRATION_CD, 'scheduled_review' => $scheduledReview->WC_SREV_CODE])`.

---

### 2.3 PUT `/reviews/scheduled/{scheduledReview}/reschedule`
Updates the scheduled date of a `PENDING` appointment.

#### Request Body:
```json
{
  "scheduled_date": "2026-10-10 10:00:00",
  "notes": "تم التعديل بناءً على طلب المريض"
}
```

#### Validation Rules:
- `scheduled_date`: `required|date`
- `notes`: `nullable|string|max:1000`

#### Response:
- **Redirect Response**: Redirects back to `/reviews/scheduled` with success message: `"تم إعادة جدولة الموعد بنجاح إلى 2026-10-10"`.
- **Error Response**: 422 Unprocessable Entity if validation fails or 400 Bad Request if appointment is not `PENDING`.

---

### 2.4 POST `/reviews/scheduled/{scheduledReview}/cancel`
Cancels a `PENDING` appointment.

#### Request Body:
```json
{
  "notes": "اعتذر المريض عن الحضور"
}
```

#### Validation Rules:
- `notes`: `nullable|string|max:1000`

#### Behavioral Logic:
```php
$scheduledReview->update([
    'WC_SREV_STATUS' => 'CANCELLED',
    'WC_SREV_NOTES' => $request->notes ? $scheduledReview->WC_SREV_NOTES . "\n[إلغاء]: " . $request->notes : $scheduledReview->WC_SREV_NOTES,
]);
```

#### Response:
- **Redirect Response**: Redirects back with success message: `"تم إلغاء موعد المراجعة بنجاح"`.

---

### 2.5 Daily Review Submission Contract Integration
When submitting `POST /daily-reviews` (`DailyReviewController@store`), if `scheduled_review_code` parameter is present:

#### Internal DB Transaction:
```php
DB::transaction(function() use ($dailyReviewData, $scheduledReviewCode) {
    $dailyReview = WcDailyReview::create($dailyReviewData);

    if ($scheduledReviewCode) {
        $scheduledReview = WcScheduledReview::where('WC_SREV_CODE', $scheduledReviewCode)
            ->where('WC_SREV_STATUS', 'PENDING')
            ->first();

        if ($scheduledReview) {
            $scheduledReview->update([
                'WC_SREV_STATUS' => 'COMPLETED',
                'WC_SREV_COMPLETED_ON' => now(),
            ]);
            $dailyReview->update([
                'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduledReview->WC_SREV_CODE,
            ]);
        }
    }
});
```
