<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PerUser extends Authenticatable
{
    use HasDbFirstAttributes;

    protected $table = 'per_users_tb';

    protected $primaryKey = 'PER_USER_CODE';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'PER_USER_CODE',
        'PER_USER_USERNAME',
        'PER_USER_PASSWORD',
        'PER_USER_ROLE',
        'PER_USER_IS_ACTIVE',
    ];

    protected $hidden = [
        'PER_USER_PASSWORD',
    ];

    public function employee()
    {
        return $this->hasOne(PerEmployee::class, 'PER_EMP_USER_CD', 'PER_USER_CODE');
    }
}
