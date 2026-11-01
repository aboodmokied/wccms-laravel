<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A ward stay inside an admission: HOS.INPATIENT_ADM_TB, key (INPATIENT_CODE, HOS_NO). Read only.
 */
class InpatientStay extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'inpatient_stays';

    protected $primaryKey = 'INPATIENT_CODE';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'INPATIENT_CODE' => 'integer',
            'INPATIENT_DATE' => 'datetime',
            'INPATIENT_OUT_DATE' => 'datetime',
        ];
    }

    public function scopeNotCancelled(Builder $query): void
    {
        $query->where($query->qualifyColumn('INPATIENT_CANCELED'), config('hospital.admission.not_cancelled_value'));
    }

    /**
     * The stay in progress: not left the ward and not cancelled.
     */
    public function scopeCurrent(Builder $query): void
    {
        $query->whereNull($query->qualifyColumn('INPATIENT_OUT_DATE'))->notCancelled();
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionPatient::class, 'INPATIENT_ADMISSION_CD', 'ADMISSION_CODE');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(HospitalLocation::class, config('hospital.admission.ward_column'), 'LOC_CODE');
    }
}
