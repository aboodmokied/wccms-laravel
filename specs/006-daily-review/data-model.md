# Data Model & Schema Mapping: Phase 6 — Daily Review

## DB-First Entity Specifications

### 1. `WcDailyReview` (`wc_daily_review_tb`)

Represents the header record for a daily follow-up visit.

| Column | Type | Nullable | Description / Relationships |
|--------|------|----------|-----------------------------|
| `WC_DREV_CODE` | `CHAR(36)` | NO | Primary Key (UUID) |
| `WC_DREV_REGISTRATION_CD` | `CHAR(36)` | NO | Foreign Key → `wc_registration_tb.WC_REG_CODE` |
| `WC_DREV_SCHEDULED_REVIEW_CD` | `CHAR(36)` | YES | Foreign Key → `wc_scheduled_review_tb.WC_SREV_CODE` |
| `WC_DREV_VISIT_DATE` | `DATETIME` | NO | Date and time of the follow-up visit |
| `WC_DREV_NURSE_CD` | `CHAR(36)` | NO | Foreign Key → `per_employee_tb.PER_EMP_CODE` (Attending Nurse) |
| `WC_DREV_ENTRY_USER_CD` | `CHAR(36)` | NO | Foreign Key → `per_users_tb.PER_USER_CODE` (Data-Entry User) |
| `WC_DREV_OVERALL_STATUS` | `VARCHAR(30)` | NO | Overall outcome: `IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, `DEATH` |
| `WC_DREV_GENERAL_NOTES` | `TEXT` | YES | General clinical notes for the visit |

**Model Configuration (`app/Models/WcDailyReview.php`)**:
```php
class WcDailyReview extends Model
{
    protected $table = 'wc_daily_review_tb';
    protected $primaryKey = 'WC_DREV_CODE';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'WC_DREV_CODE',
        'WC_DREV_REGISTRATION_CD',
        'WC_DREV_SCHEDULED_REVIEW_CD',
        'WC_DREV_VISIT_DATE',
        'WC_DREV_NURSE_CD',
        'WC_DREV_ENTRY_USER_CD',
        'WC_DREV_OVERALL_STATUS',
        'WC_DREV_GENERAL_NOTES',
    ];

    public function registration()
    {
        return $this->belongsTo(WcRegistration::class, 'WC_DREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function details()
    {
        return $this->hasMany(WcDailyReviewDetail::class, 'WC_DREVD_DAILY_REVIEW_CD', 'WC_DREV_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_DREV_NURSE_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_DREV_ENTRY_USER_CD', 'PER_USER_CODE');
    }
}
```

---

### 2. `WcDailyReviewDetail` (`wc_daily_review_detail_tb`)

Represents per-wound evaluation details within a daily follow-up visit.

| Column | Type | Nullable | Description / Relationships |
|--------|------|----------|-----------------------------|
| `WC_DREVD_CODE` | `CHAR(36)` | NO | Primary Key (UUID) |
| `WC_DREVD_DAILY_REVIEW_CD` | `CHAR(36)` | NO | Foreign Key → `wc_daily_review_tb.WC_DREV_CODE` |
| `WC_DREVD_REG_DIAGNOSIS_CD` | `CHAR(36)` | NO | Foreign Key → `wc_reg_diagnosis_tb.WC_RDIAG_CODE` |
| `WC_DREVD_DRESSING_CD` | `CHAR(36)` | YES | Foreign Key → `wc_dressing_tb.WC_DRESS_CODE` |
| `WC_DREVD_MEDICATION_CD` | `CHAR(36)` | YES | Foreign Key → `wc_medication_tb.WC_MED_CODE` |
| `WC_DREVD_OBSERVATION` | `TEXT` | YES | Observed clinical changes for this wound |
| `WC_DREVD_OUTCOME_STATUS` | `VARCHAR(30)` | NO | Per-wound status: `IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED` |

**Model Configuration (`app/Models/WcDailyReviewDetail.php`)**:
```php
class WcDailyReviewDetail extends Model
{
    protected $table = 'wc_daily_review_detail_tb';
    protected $primaryKey = 'WC_DREVD_CODE';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'WC_DREVD_CODE',
        'WC_DREVD_DAILY_REVIEW_CD',
        'WC_DREVD_REG_DIAGNOSIS_CD',
        'WC_DREVD_DRESSING_CD',
        'WC_DREVD_MEDICATION_CD',
        'WC_DREVD_OBSERVATION',
        'WC_DREVD_OUTCOME_STATUS',
    ];

    public function dailyReview()
    {
        return $this->belongsTo(WcDailyReview::class, 'WC_DREVD_DAILY_REVIEW_CD', 'WC_DREV_CODE');
    }

    public function woundDiagnosis()
    {
        return $this->belongsTo(WcRegDiagnosis::class, 'WC_DREVD_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function dressing()
    {
        return $this->belongsTo(WcDressing::class, 'WC_DREVD_DRESSING_CD', 'WC_DRESS_CODE');
    }

    public function medication()
    {
        return $this->belongsTo(WcMedication::class, 'WC_DREVD_MEDICATION_CD', 'WC_MED_CODE');
    }
}
```

---

### 3. Entity State Transitions

```text
Registration Status (wc_registration_tb.WC_REG_STATUS)
   ACTIVE
      │
      ├── Submit Daily Review with overall outcome: WORSENED_STAY
      │      └── Status remains ACTIVE
      │
      ├── Submit Daily Review with overall outcome: IMPROVED_DISCHARGE
      │      └── Status transitions to DISCHARGED
      │
      └── Submit Daily Review with overall outcome: IMPROVED_SCHEDULED
             ├── Status transitions to DISCHARGED
             └── System creates WC_SCHEDULED_REVIEW_TB (status = PENDING)
```
