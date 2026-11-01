<?php

namespace App\Models;

use App\Models\Concerns\HasDbFirstAttributes;
use App\Models\Concerns\LinksHospitalEmployee;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PerUser extends Authenticatable
{
    use HasDbFirstAttributes, LinksHospitalEmployee;

    public const HOSPITAL_NO_COLUMN = 'PER_USER_HOS_NO';

    protected static bool $scopedToHospital = false;

    protected $table = 'wc_users_tb';

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
        'PER_USER_EMP_CD',
        'PER_USER_HOS_NO',
    ];

    protected $hidden = [
        'PER_USER_PASSWORD',
    ];
}
