<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A hospital admission: HOS.ADMISSION_PATIENT_TB, key (ADMISSION_CODE, HOS_NO). Read only.
 */
class AdmissionPatient extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'admissions';

    protected $primaryKey = 'ADMISSION_CODE';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ADMISSION_CODE' => 'integer',
            'ADMISSION_ENTER_DATE' => 'datetime',
            'ADMISSION_OUT_DATE' => 'datetime',
        ];
    }

    /**
     * Still in hospital: no discharge date and at least one stay that is not cancelled (HOS.INPATIENT_VW rule).
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull($query->qualifyColumn('ADMISSION_OUT_DATE'))
            ->whereHas('stays', fn (Builder $stays) => $stays->notCancelled());
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(MrPatient::class, 'ADMISSION_PATREC_CD', 'MRP_PATREC_CODE');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, config('hospital.admission.doctor_column'), 'EMP_SERIAL');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(HospitalLocation::class, 'ADMISSION_LOC_CD', 'LOC_CODE');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(InpatientStay::class, 'INPATIENT_ADMISSION_CD', 'ADMISSION_CODE');
    }

    /**
     * The ward stay in progress: no INPATIENT_OUT_DATE and not cancelled; the latest one wins.
     */
    public function currentStay(): HasOne
    {
        return $this->hasOne(InpatientStay::class, 'INPATIENT_ADMISSION_CD', 'ADMISSION_CODE')
            ->ofMany(
                ['INPATIENT_DATE' => 'max', 'INPATIENT_CODE' => 'max'],
                fn (Builder $query) => $query->current()
            );
    }
}
