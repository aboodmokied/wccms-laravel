<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WcRegDiagnosis extends Model
{
    use BelongsToHospital, HasDbFirstAttributes;

    public const HOSPITAL_NO_COLUMN = 'WC_RDIAG_HOS_NO';

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
        'WC_RDIAG_HOS_NO',
    ];

    /**
     * Patient and employee codes on this row, checked against the configured hospital before every save.
     *
     * @return array<string, class-string<Model>>
     */
    protected function hospitalLinks(): array
    {
        return [
            'WC_RDIAG_CLINICAL_EMP_CD' => Employee::class,
        ];
    }

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
        return $this->belongsTo(Employee::class, 'WC_RDIAG_CLINICAL_EMP_CD', 'EMP_SERIAL');
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

    public function assessments(): HasMany
    {
        return $this->hasMany(WcWoundAssessment::class, 'WC_WA_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function initialAssessment(): HasOne
    {
        return $this->hasOne(WcWoundAssessment::class, 'WC_WA_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE')
            ->where('WC_WA_TYPE', 'INITIAL');
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(WcWoundAssessment::class, 'WC_WA_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE')
            ->latestOfMany('WC_WA_ASSESSED_AT');
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
