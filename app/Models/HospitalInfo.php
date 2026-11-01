<?php

namespace App\Models;

use App\Models\Concerns\HospitalTable;
use Illuminate\Database\Eloquent\Model;

/**
 * The hospitals list: HOS.C_HOSPITAL_TB. H_CURR = 1 marks the hospital this database belongs to. Read only, used by
 * the startup check (wccms:check-hospital).
 */
class HospitalInfo extends Model
{
    use HospitalTable;

    public const HOSPITAL_TABLE = 'hospitals';

    protected $primaryKey = 'H_CODE';

    protected $guarded = [];
}
