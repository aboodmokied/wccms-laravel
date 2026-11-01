<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    /**
     * Primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'PER_EMP_CODE';

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
     * Get the associated user account; the link lives on PER_USERS_TB.PER_USER_EMP_CD.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'PER_USER_EMP_CD', 'PER_EMP_CODE');
    }
}
