<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WcDailyReviewDetail extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_review_detail_tb';

    protected $primaryKey = 'WC_REVD_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_REVD_CODE',
        'WC_REVD_REVIEW_CD',
        'WC_REVD_REVIEW_TYPE',
        'WC_DREVD_REG_DIAGNOSIS_CD',
        'WC_DREVD_WOUND_DESCRIPTION',
        'WC_DREVD_WOUND_CHANGES',
        'WC_DREVD_DRESSING_CD',
        'WC_DREVD_MEDICATION_CD',
        'WC_DREVD_STATUS',
        'WC_DREVD_FOLLOW_UP_REQUIRED',
    ];

    public function dailyReview()
    {
        return $this->belongsTo(WcDailyReview::class, 'WC_REVD_REVIEW_CD', 'WC_DREV_CODE');
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

    public function woundPhotos()
    {
        return $this->hasMany(WcWoundPhoto::class, 'WC_PHOTO_DAILY_REVIEW_DETAIL_CD', 'WC_REVD_CODE');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(WcWoundAssessment::class, 'WC_WA_REVIEW_DETAIL_CD', 'WC_REVD_CODE');
    }

    /**
     * Backward compatibility accessor for outcome status.
     */
    public function getWcDrevdOutcomeStatusAttribute(): ?string
    {
        return $this->attributes['WC_DREVD_STATUS'] ?? null;
    }

    /**
     * Backward compatibility accessor for observation text.
     */
    public function getWcDrevdObservationAttribute(): ?string
    {
        return $this->attributes['WC_DREVD_WOUND_DESCRIPTION'] ?? null;
    }
}
