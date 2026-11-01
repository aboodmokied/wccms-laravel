<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A hospital ward or clinic: HOS.C_LOCATION_TB, key (LOC_CODE, HOS_NO). Read only.
 */
class HospitalLocation extends Model
{
    use HasFactory, HospitalTable;

    public const HOSPITAL_TABLE = 'locations';

    protected $primaryKey = 'LOC_CODE';

    protected $guarded = [];
}
