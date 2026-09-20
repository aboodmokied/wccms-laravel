<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcDressing extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_dressing_tb';

    protected $primaryKey = 'WC_DRESS_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_DRESS_CODE',
        'WC_DRESS_NAME',
        'WC_DRESS_IS_ACTIVE',
    ];
}
