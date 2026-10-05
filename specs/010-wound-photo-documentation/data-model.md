# Phase 1 Data Model & Entity Specification: Wound Photo Documentation (Phase 10)

## Entity Relationship Diagram

```
+--------------------------+          1 : N          +--------------------------+
|   WC_REG_DIAGNOSIS_TB    | ----------------------> |    WC_WOUND_PHOTO_TB     |
+--------------------------+                         +--------------------------+
             ^                                                    |
             | 1                                                1 |
             |                                                    |
             | N                                                  v
+--------------------------+                         +--------------------------+
|WC_DAILY_REVIEW_DETAIL_TB | ----------------------> |    PER_EMPLOYEE_TB /     |
+--------------------------+          0..1 : N       |      PER_USERS_TB        |
                                                     +--------------------------+
```

---

## Database Table Definition: `WC_WOUND_PHOTO_TB` (`wc_wound_photo_tb`)

### Eloquent Model: `App\Models\WcWoundPhoto`

| Column Name | Data Type | Key / Constraint | Description |
|---|---|---|---|
| `WC_PHOTO_CODE` | `CHAR(36)` | PK | Unique UUID identifier |
| `WC_PHOTO_REG_DIAGNOSIS_CD` | `CHAR(36)` | FK -> `WC_REG_DIAGNOSIS_TB` | Mandatory link to wound diagnosis |
| `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` | `CHAR(36)` | FK -> `WC_DAILY_REVIEW_DETAIL_TB` | Nullable link to daily review detail (`NULL` for initial baseline) |
| `WC_PHOTO_STAGE` | `VARCHAR(20)` | Not Null | Photo stage (`INITIAL` or `REVIEW`) |
| `WC_PHOTO_PATH` | `VARCHAR(255)` | Not Null | Relative storage path (`wound_photos/YYYY/MM/uuid.jpg`) |
| `WC_PHOTO_CAPTION` | `TEXT` | Nullable | Optional clinical notes or image description |
| `WC_PHOTO_TAKEN_AT` | `DATETIME` | Not Null | Timestamp when photo was taken/uploaded |
| `WC_PHOTO_NURSE_CD` | `CHAR(36)` | FK -> `PER_EMPLOYEE_TB` | Examining/Attending Nurse employee code |
| `WC_PHOTO_ENTRY_USER_CD` | `CHAR(36)` | FK -> `PER_USERS_TB` | Authenticated data-entry user code |

---

## Model Definition Syntax (`WcWoundPhoto.php`)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WcWoundPhoto extends Model
{
    protected $table = 'wc_wound_photo_tb';
    protected $primaryKey = 'WC_PHOTO_CODE';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'WC_PHOTO_CODE',
        'WC_PHOTO_REG_DIAGNOSIS_CD',
        'WC_PHOTO_DAILY_REVIEW_DETAIL_CD',
        'WC_PHOTO_STAGE',
        'WC_PHOTO_PATH',
        'WC_PHOTO_CAPTION',
        'WC_PHOTO_TAKEN_AT',
        'WC_PHOTO_NURSE_CD',
        'WC_PHOTO_ENTRY_USER_CD',
    ];

    public function regDiagnosis()
    {
        return $this->belongsTo(WcRegDiagnosis::class, 'WC_PHOTO_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function dailyReviewDetail()
    {
        return $this->belongsTo(WcDailyReviewDetail::class, 'WC_PHOTO_DAILY_REVIEW_DETAIL_CD', 'WC_REVD_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_PHOTO_NURSE_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_PHOTO_ENTRY_USER_CD', 'PER_USER_CODE');
    }
}
```
