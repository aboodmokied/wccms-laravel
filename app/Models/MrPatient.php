<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class MrPatient extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'mr_patient_tb';

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
        'MR_PAT_LAST_SYNCED_AT',
    ];

    public function requests()
    {
        return $this->hasMany(WcRequestReg::class, 'WC_REQ_PATIENT_CD', 'MR_PAT_CODE');
    }

    public function registrations()
    {
        return $this->hasMany(WcRegistration::class, 'WC_REG_PATIENT_CD', 'MR_PAT_CODE');
    }
}
