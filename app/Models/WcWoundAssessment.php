<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcWoundAssessment extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_wound_assessment_tb';

    protected $primaryKey = 'WC_WA_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_WA_CODE',
        'WC_WA_REG_DIAGNOSIS_CD',
        'WC_WA_REVIEW_DETAIL_CD',
        'WC_WA_TYPE',
        'WC_WA_CLINICAL_EMP_CD',
        'WC_WA_WCT_EMP_CD',
        'WC_WA_DOCTOR_EMP_CD',
        'WC_WA_ENTRY_USER_CD',
        'WC_WA_ASSESSED_AT',
        'WC_WA_WOUND_STATUS_CD',
        'WC_WA_EXUDATE_AMOUNT_CD',
        'WC_WA_EXUDATE_TYPE_CD',
        'WC_WA_ODOR_CD',
        'WC_WA_PAIN_SEVERITY_CD',
        'WC_WA_PAIN_AT_DRESSING',
        'WC_WA_FEVER_CD',
        'WC_WA_LENGTH_MM',
        'WC_WA_WIDTH_MM',
        'WC_WA_DEPTH_MM',
        'WC_WA_LOCATION_CD',
        'WC_WA_LATERALITY_CD',
        'WC_WA_LOCATION_NOTES',
        'WC_WA_SWAB_TAKEN',
        'WC_WA_NUTRITION_CD',
        'WC_WA_DRESSING_CD',
        'WC_WA_MEDICATION_CD',
        'WC_WA_PROCEDURE_CD',
        'WC_WA_TREATMENT_NOTES',
        'WC_WA_CLINICAL_OBSERVATION',
        'WC_WA_WOUND_CHANGES',
        'WC_WA_FOLLOW_UP_REQUIRED',
    ];

    protected function casts(): array
    {
        return [
            'WC_WA_ASSESSED_AT' => 'datetime',
            'WC_WA_PAIN_AT_DRESSING' => 'boolean',
            'WC_WA_SWAB_TAKEN' => 'boolean',
            'WC_WA_FOLLOW_UP_REQUIRED' => 'boolean',
            'WC_WA_LENGTH_MM' => 'decimal:2',
            'WC_WA_WIDTH_MM' => 'decimal:2',
            'WC_WA_DEPTH_MM' => 'decimal:2',
        ];
    }

    public function regDiagnosis(): BelongsTo
    {
        return $this->belongsTo(WcRegDiagnosis::class, 'WC_WA_REG_DIAGNOSIS_CD', 'WC_RDIAG_CODE');
    }

    public function reviewDetail(): BelongsTo
    {
        return $this->belongsTo(WcDailyReviewDetail::class, 'WC_WA_REVIEW_DETAIL_CD', 'WC_REVD_CODE');
    }

    public function clinicalEmployee(): BelongsTo
    {
        return $this->belongsTo(PerEmployee::class, 'WC_WA_CLINICAL_EMP_CD', 'PER_EMP_CODE');
    }

    public function woundCareTeamEmployee(): BelongsTo
    {
        return $this->belongsTo(PerEmployee::class, 'WC_WA_WCT_EMP_CD', 'PER_EMP_CODE');
    }

    public function doctorEmployee(): BelongsTo
    {
        return $this->belongsTo(PerEmployee::class, 'WC_WA_DOCTOR_EMP_CD', 'PER_EMP_CODE');
    }

    public function entryUser(): BelongsTo
    {
        return $this->belongsTo(PerUser::class, 'WC_WA_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function woundStatus(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_WOUND_STATUS_CD', 'WC_ACH_CODE');
    }

    public function exudateAmount(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_EXUDATE_AMOUNT_CD', 'WC_ACH_CODE');
    }

    public function exudateType(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_EXUDATE_TYPE_CD', 'WC_ACH_CODE');
    }

    public function odor(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_ODOR_CD', 'WC_ACH_CODE');
    }

    public function painSeverity(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_PAIN_SEVERITY_CD', 'WC_ACH_CODE');
    }

    public function fever(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_FEVER_CD', 'WC_ACH_CODE');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_LOCATION_CD', 'WC_ACH_CODE');
    }

    public function laterality(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_LATERALITY_CD', 'WC_ACH_CODE');
    }

    public function nutrition(): BelongsTo
    {
        return $this->belongsTo(WcAssessmentChoice::class, 'WC_WA_NUTRITION_CD', 'WC_ACH_CODE');
    }

    public function dressing(): BelongsTo
    {
        return $this->belongsTo(WcDressing::class, 'WC_WA_DRESSING_CD', 'WC_DRESS_CODE');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(WcMedication::class, 'WC_WA_MEDICATION_CD', 'WC_MED_CODE');
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(WcProcedure::class, 'WC_WA_PROCEDURE_CD', 'WC_PROC_CODE');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(WcAssessmentFinding::class, 'WC_AF_ASSESSMENT_CD', 'WC_WA_CODE');
    }

    public function woundPhotos(): HasMany
    {
        return $this->hasMany(WcWoundPhoto::class, 'WC_PHOTO_ASSESSMENT_CD', 'WC_WA_CODE');
    }
}
