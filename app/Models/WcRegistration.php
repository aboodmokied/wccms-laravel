<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcRegistration extends Model
{
    use HasDbFirstAttributes;

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
        'WC_REG_DATE',
        'WC_REG_NURSE_CD',
        'WC_REG_ENTRY_USER_CD',
        'WC_REG_STATUS',
        'WC_REG_DISCHARGED_AT',
        'WC_REG_DISCHARGED_BY_EMP_CD',
    ];

    public function request()
    {
        return $this->belongsTo(WcRequestReg::class, 'WC_REG_REQUEST_CD', 'WC_REQ_CODE');
    }

    public function patient()
    {
        return $this->belongsTo(MrPatient::class, 'WC_REG_PATIENT_CD', 'MR_PAT_CODE');
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
        return $this->belongsTo(PerEmployee::class, 'WC_REG_NURSE_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_REG_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function dischargingNurse()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_REG_DISCHARGED_BY_EMP_CD', 'PER_EMP_CODE');
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
}
