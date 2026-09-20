<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class WcDepartment extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'wc_department_tb';

    protected $primaryKey = 'WC_DEPT_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'WC_DEPT_CODE',
        'WC_DEPT_NAME',
        'WC_DEPT_IS_ACTIVE',
    ];
}
