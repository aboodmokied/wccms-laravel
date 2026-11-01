<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Model;

/**
 * A job title: HOS.C_JOB_TB, key (JOB_CODE, HOS_NO). Read only.
 */
class JobLookup extends Model
{
    use HospitalTable;

    public const HOSPITAL_TABLE = 'jobs';

    protected $primaryKey = 'JOB_CODE';

    protected $guarded = [];
}
