<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcDiagnosis extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_diagnosis_tb';

    protected $primaryKey = 'WC_DIAG_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_DIAG_CODE',
        'WC_DIAG_NAME',
        'WC_DIAG_IS_ACTIVE',
    ];
}
