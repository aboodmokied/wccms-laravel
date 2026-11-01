<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A sex code: HOS.C_SEX_TB, key (SEX_CODE, HOS_NO). Read only.
 *
 * No code is assumed (the hospital's own code disagrees on them): SEX_NAME_AR is shown on screens, and reports
 * classify by an exact match of SEX_NAME_EN against config('hospital.sex').
 */
class SexLookup extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'sexes';

    public const MALE = 'MALE';

    public const FEMALE = 'FEMALE';

    public const UNKNOWN = 'UNKNOWN';

    protected $primaryKey = 'SEX_CODE';

    protected $guarded = [];

    /**
     * MALE, FEMALE, or UNKNOWN when SEX_NAME_EN is empty or not exactly one of the configured names.
     */
    public function classification(): string
    {
        return match ($this->SEX_NAME_EN) {
            config('hospital.sex.male_name_en') => self::MALE,
            config('hospital.sex.female_name_en') => self::FEMALE,
            default => self::UNKNOWN,
        };
    }
}
