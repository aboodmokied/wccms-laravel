<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Database\Eloquent\Model;

class PerEmployee extends Model
{
    use HasDbFirstAttributes;

    protected $primaryKey = 'PER_EMP_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'PER_EMP_CODE',
        'PER_EMP_FULL_NAME',
        'PER_EMP_JOB_TITLE',
    ];

    /**
     * The hospital employees table, read directly (config/hospital.php).
     */
    public function getTable(): string
    {
        return config('hospital.tables.employees');
    }

    /**
     * The WCCMS account linked to this employee; the link lives on PER_USERS_TB.PER_USER_EMP_CD.
     */
    public function user()
    {
        return $this->hasOne(PerUser::class, 'PER_USER_EMP_CD', 'PER_EMP_CODE');
    }
}
