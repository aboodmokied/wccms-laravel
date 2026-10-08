<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcRegDiagnosis extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_reg_diagnosis_tb';

    protected $primaryKey = 'WC_RDIAG_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_RDIAG_CODE',
        'WC_RDIAG_REGISTRATION_CD',
        'WC_RDIAG_DIAGNOSIS_CD',
        'WC_RDIAG_WOUND_TYPE_CD',
        'WC_RDIAG_DESCRIPTION',
        'WC_RDIAG_CLINICAL_EMP_CD',
        'WC_RDIAG_ENTRY_USER_CD',
    ];

    public function registration()
    {
        return $this->belongsTo(WcRegistration::class, 'WC_RDIAG_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function diagnosis()
    {
        return $this->belongsTo(WcDiagnosis::class, 'WC_RDIAG_DIAGNOSIS_CD', 'WC_DIAG_CODE');
    }

    public function woundType()
    {
        return $this->belongsTo(WcWoundType::class, 'WC_RDIAG_WOUND_TYPE_CD', 'WC_WTYPE_CODE');
    }

    public function clinicalEmployee()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_RDIAG_CLINICAL_EMP_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_RDIAG_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function dailyReviewDetails()
    {
        return $this->hasMany(WcDailyReviewDetail::class, 'WC_DREVD_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function woundPhotos()
    {
        return $this->hasMany(WcWoundPhoto::class, 'WC_PHOTO_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function getLatestDetailAttribute(): ?WcDailyReviewDetail
    {
        if ($this->relationLoaded('dailyReviewDetails')) {
            return $this->dailyReviewDetails
                ->sortByDesc(fn ($detail) => $detail->dailyReview?->WC_DREV_VISIT_DATE)
                ->first();
        }

        return $this->dailyReviewDetails()
            ->whereHas('dailyReview')
            ->get()
            ->sortByDesc(fn ($detail) => $detail->dailyReview?->WC_DREV_VISIT_DATE)
            ->first();
    }
}
