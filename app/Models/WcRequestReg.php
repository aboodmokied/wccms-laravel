<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcRequestReg extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_request_reg_tb';

    protected $primaryKey = 'WC_REQ_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_REQ_CODE',
        'WC_REQ_PATIENT_CD',
        'WC_REQ_PAT_NAME_SNAPSHOT',
        'WC_REQ_PAT_ID_SNAPSHOT',
        'WC_REQ_PAT_PHONE_SNAPSHOT',
        'WC_REQ_NURSE_CD',
        'WC_REQ_ENTRY_USER_CD',
        'WC_REQ_REVIEWED_BY_CD',
        'WC_REQ_STATUS',
        'WC_REQ_NOTES',
        'WC_REQ_CREATED_AT',
    ];

    public function patient()
    {
        return $this->belongsTo(MrPatient::class, 'WC_REQ_PATIENT_CD', 'MR_PAT_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_REQ_NURSE_CD', 'PER_EMP_CODE');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_REQ_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function reviewer()
    {
        return $this->belongsTo(PerEmployee::class, 'WC_REQ_REVIEWED_BY_CD', 'PER_EMP_CODE');
    }

    public function registration()
    {
        return $this->hasOne(WcRegistration::class, 'WC_REG_REQUEST_CD', 'WC_REQ_CODE');
    }
}
