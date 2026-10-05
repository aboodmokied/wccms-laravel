<?php

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
