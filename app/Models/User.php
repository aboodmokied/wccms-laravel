<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'per_users_tb';

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
     * Get the associated employee profile.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'PER_EMP_USER_CD', 'PER_USER_CODE');
    }

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
