<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class PerEmployee extends Model
{
    use HasDbFirstAttributes;

    protected $table = 'per_employee_tb';

    protected $primaryKey = 'PER_EMP_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'PER_EMP_CODE',
        'PER_EMP_FULL_NAME',
        'PER_EMP_JOB_TITLE',
        'PER_EMP_USER_CD',
    ];

    public function user()
    {
        return $this->belongsTo(PerUser::class, 'PER_EMP_USER_CD', 'PER_USER_CODE');
    }
}
