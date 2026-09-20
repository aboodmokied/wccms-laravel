<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcMedication extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_medication_tb';

    protected $primaryKey = 'WC_MED_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_MED_CODE',
        'WC_MED_NAME',
        'WC_MED_IS_ACTIVE',
    ];
}
