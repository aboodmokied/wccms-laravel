<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcPmh extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_pmh_tb';

    protected $primaryKey = 'WC_PMH_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_PMH_CODE',
        'WC_PMH_NAME',
        'WC_PMH_IS_ACTIVE',
    ];
}
