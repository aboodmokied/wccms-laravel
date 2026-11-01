<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrPatient extends Model
{
    use HasDbFirstAttributes;

    protected $primaryKey = 'MR_PAT_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'MR_PAT_CODE',
        'MR_PAT_ID_NO',
        'MR_PAT_FULL_NAME',
        'MR_PAT_PHONE',
        'MR_PAT_GENDER',
        'MR_PAT_DOB',
        'MR_PAT_SUPERVISING_DOCTOR_CD',
        'MR_PAT_LAST_SYNCED_AT',
    ];

    /**
     * The hospital patients table, read directly (config/hospital.php).
     */
    public function getTable(): string
    {
        return config('hospital.tables.patients');
    }

    public function requests()
    {
        return $this->hasMany(WcRequestReg::class, 'WC_REQ_PATIENT_CD', 'MR_PAT_CODE');
    }

    public function registrations()
    {
        return $this->hasMany(WcRegistration::class, 'WC_REG_PATIENT_CD', 'MR_PAT_CODE');
    }

    /**
     * The supervising doctor, an employee in the hospital employees table.
     */
    public function supervisingDoctor(): BelongsTo
    {
        return $this->belongsTo(PerEmployee::class, 'MR_PAT_SUPERVISING_DOCTOR_CD', 'PER_EMP_CODE');
    }
}
