<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcWoundType extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_wound_type_tb';

    protected $primaryKey = 'WC_WTYPE_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_WTYPE_CODE',
        'WC_WTYPE_NAME',
        'WC_WTYPE_IS_ACTIVE',
    ];
}
