<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcProcedure extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_procedure_tb';

    protected $primaryKey = 'WC_PROC_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_PROC_CODE',
        'WC_PROC_NAME',
        'WC_PROC_IS_ACTIVE',
    ];
}
