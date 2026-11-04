<?php

namespace App\Models;

use App\Models\Concerns\LinksHospitalEmployee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, LinksHospitalEmployee, Notifiable;

    /**
     * Hospital number of the employee link (PER_USER_EMP_CD). Accounts themselves are not filtered by it: the only
     * account is the system one (App\Support\SystemUser), found by its code whatever hospital is configured.
     */
    public const HOSPITAL_NO_COLUMN = 'PER_USER_HOS_NO';

    protected static bool $scopedToHospital = false;

    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'wc_users_tb';

    /**
     * Primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'PER_USER_CODE';

    /**
     * Indicates if the PK is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PER_USER_CODE',
        'PER_USER_USERNAME',
        'PER_USER_ROLE',
        'PER_USER_IS_ACTIVE',
        'PER_USER_EMP_CD',
        'PER_USER_HOS_NO',
    ];

    /**
     * Attribute casting definitions.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'PER_USER_IS_ACTIVE' => 'boolean',
    ];

    /**
     * Determine if the user has the ADMIN role.
     */
    public function isAdmin(): bool
    {
        return $this->PER_USER_ROLE === 'ADMIN';
    }

    /**
     * Determine if the user has the NURSE role.
     */
    public function isNurse(): bool
    {
        return $this->PER_USER_ROLE === 'NURSE';
    }
}
