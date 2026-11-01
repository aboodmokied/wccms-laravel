<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'PER_USER_EMP_CD',
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
     * Get the associated hospital employee profile (PER_USER_EMP_CD).
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'PER_USER_EMP_CD', 'PER_EMP_CODE');
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
