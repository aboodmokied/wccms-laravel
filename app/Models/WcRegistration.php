<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use App\Models\Concerns\HasDbFirstAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcRegistration extends Model
{
    use BelongsToHospital, HasDbFirstAttributes;

    public const HOSPITAL_NO_COLUMN = 'WC_REG_HOS_NO';

    protected $table = 'wc_registration_tb';

    protected $primaryKey = 'WC_REG_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_REG_CODE',
        'WC_REG_REQUEST_CD',
        'WC_REG_PATIENT_CD',
        'WC_REG_DEPARTMENT_CD',
        'WC_REG_PROCEDURE_CD',
        'WC_REG_PMH_NOTES',
        'WC_REG_HAS_ALLERGIES',
        'WC_REG_ALLERGIES_DESCRIPTION',
        'WC_REG_NO_COMORBIDITIES',
        'WC_REG_NEXT_VISIT_DATE',
        'WC_REG_FOLLOW_UP_FREQUENCY',
        'WC_REG_FOLLOW_UP_OBJECTIVE',
        'WC_REG_DATE',
        'WC_REG_NURSE_CD',
        'WC_REG_ENTRY_USER_CD',
        'WC_REG_STATUS',
        'WC_REG_DISCHARGED_AT',
        'WC_REG_DISCHARGED_BY_EMP_CD',
        'WC_REG_HOS_NO',
    ];

    protected function casts(): array
    {
        return [
            'WC_REG_DATE' => 'datetime',
            'WC_REG_HAS_ALLERGIES' => 'boolean',
            'WC_REG_NO_COMORBIDITIES' => 'boolean',
            'WC_REG_NEXT_VISIT_DATE' => 'datetime',
            'WC_REG_DISCHARGE_DATE' => 'date',
            'WC_REG_DISCHARGED_AT' => 'datetime',
        ];
    }

    /**
     * Patient and employee codes on this row, checked against the configured hospital before every save.
     *
     * @return array<string, class-string<Model>>
     */
    protected function hospitalLinks(): array
    {
        return [
            'WC_REG_PATIENT_CD' => MrPatient::class,
            'WC_REG_PROC_PERFORMED_BY_CD' => Employee::class,
            'WC_REG_NURSE_CD' => Employee::class,
            'WC_REG_DISCHARGED_BY_EMP_CD' => Employee::class,
        ];
    }

    public function pmhLinks(): HasMany
    {
        return $this->hasMany(WcRegistrationPmh::class, 'WC_RPMH_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function pmhConditions(): BelongsToMany
    {
        return $this->belongsToMany(
            WcPmh::class,
            'wc_registration_pmh_tb',
            'WC_RPMH_REGISTRATION_CD',
            'WC_RPMH_PMH_CD',
            'WC_REG_CODE',
            'WC_PMH_CODE'
        );
    }

    public function request()
    {
        return $this->belongsTo(WcRequestReg::class, 'WC_REG_REQUEST_CD', 'WC_REQ_CODE');
    }

    public function patient()
    {
        return $this->belongsTo(MrPatient::class, 'WC_REG_PATIENT_CD', 'MRP_PATREC_CODE');
    }

    public function department()
    {
        return $this->belongsTo(WcDepartment::class, 'WC_REG_DEPARTMENT_CD', 'WC_DEPT_CODE');
    }

    public function procedure()
    {
        return $this->belongsTo(WcProcedure::class, 'WC_REG_PROCEDURE_CD', 'WC_PROC_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(Employee::class, 'WC_REG_NURSE_CD', 'EMP_SERIAL');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_REG_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function dischargingNurse()
    {
        return $this->belongsTo(Employee::class, 'WC_REG_DISCHARGED_BY_EMP_CD', 'EMP_SERIAL');
    }

    public function diagnoses()
    {
        return $this->hasMany(WcRegDiagnosis::class, 'WC_RDIAG_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function dailyReviews()
    {
        return $this->hasMany(WcDailyReview::class, 'WC_DREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    public function scheduledReviews()
    {
        return $this->hasMany(WcScheduledReview::class, 'WC_SREV_REGISTRATION_CD', 'WC_REG_CODE');
    }

    /**
     * Check if patient has been reviewed today.
     */
    public function isReviewedToday(): bool
    {
        if ($this->relationLoaded('dailyReviews')) {
            return $this->dailyReviews->contains(function ($rev) {
                return Carbon::parse($rev->WC_DREV_VISIT_DATE)->isToday();
            });
        }

        return $this->dailyReviews()
            ->whereDate('WC_DREV_VISIT_DATE', now()->toDateString())
            ->exists();
    }

    /**
     * Accessor for is_reviewed_today.
     */
    public function getIsReviewedTodayAttribute(): bool
    {
        return $this->isReviewedToday();
    }

    /**
     * Get today's review instance if exists.
     */
    public function getTodayReviewAttribute()
    {
        if ($this->relationLoaded('dailyReviews')) {
            return $this->dailyReviews->first(function ($rev) {
                return Carbon::parse($rev->WC_DREV_VISIT_DATE)->isToday();
            });
        }

        return $this->dailyReviews()
            ->whereDate('WC_DREV_VISIT_DATE', now()->toDateString())
            ->latest('WC_DREV_VISIT_DATE')
            ->first();
    }
}
