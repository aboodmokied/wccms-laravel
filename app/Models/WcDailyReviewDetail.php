<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcDailyReviewDetail extends Model
{
    use HasDbFirstAttributes;

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

    public function regDiagnosis()
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
