<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'per_employee_tb';

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
        'PER_EMP_USER_CD',
    ];

    /**
     * Get the associated user account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'PER_EMP_USER_CD', 'PER_USER_CODE');
    }
}
