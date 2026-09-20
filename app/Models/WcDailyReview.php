<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcDailyReview extends Model
{
    use HasDbFirstAttributes;

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
        'WC_DREV_GENERAL_NOTES',
    ];

    public function registration()
    {
        return $this->belongsTo(WcRegistration::class, 'WC_DREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function scheduledReview()
    {
        return $this->belongsTo(WcScheduledReview::class, 'WC_DREV_SCHEDULED_REVIEW_CD', 'WC_SREV_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_DREV_NURSE_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_DREV_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function details()
    {
        return $this->hasMany(WcDailyReviewDetail::class, 'WC_DREVD_DAILY_REVIEW_CD', 'WC_DREV_CODE');
    }
}
