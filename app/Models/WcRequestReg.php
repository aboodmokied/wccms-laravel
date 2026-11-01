<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcRequestReg extends Model
{
    use BelongsToHospital, HasDbFirstAttributes;

    public const HOSPITAL_NO_COLUMN = 'WC_REQ_HOS_NO';

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
        'WC_REQ_HOS_NO',
    ];

    /**
     * Patient and employee codes on this row, checked against the configured hospital before every save.
     *
     * @return array<string, class-string<Model>>
     */
    protected function hospitalLinks(): array
    {
        return [
            'WC_REQ_PATIENT_CD' => MrPatient::class,
            'WC_REQ_NURSE_CD' => Employee::class,
            'WC_REQ_REVIEWED_BY_CD' => Employee::class,
        ];
    }

    public function patient()
    {
        return $this->belongsTo(MrPatient::class, 'WC_REQ_PATIENT_CD', 'MRP_PATREC_CODE');
    }

    public function nurse()
    {
        return $this->belongsTo(Employee::class, 'WC_REQ_NURSE_CD', 'EMP_SERIAL');
    }

    public function entryUser()
    {
        return $this->belongsTo(PerUser::class, 'WC_REQ_ENTRY_USER_CD', 'PER_USER_CODE');
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'WC_REQ_REVIEWED_BY_CD', 'EMP_SERIAL');
    }

    public function registration()
    {
        return $this->hasOne(WcRegistration::class, 'WC_REG_REQUEST_CD', 'WC_REQ_CODE');
    }
}
