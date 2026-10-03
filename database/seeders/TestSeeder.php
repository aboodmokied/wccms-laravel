<?php

namespace Database\Seeders;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\PerUser;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $u = PerUser::create([
            'PER_USER_CODE' => '0062a87f-37cb-4c45-aeff-fb1dbf4f82ee',
            'PER_USER_USERNAME' => 'nurse_sarah',
            'PER_USER_ROLE' => 'NURSE',
        ]);

        $e = PerEmployee::create([
            'PER_EMP_CODE' => '658ca0b1-78e6-46ce-bee7-866409bad17c',
            'PER_EMP_FULL_NAME' => 'الممرضة سارة احمد',
            'PER_EMP_USER_CD' => $u->PER_USER_CODE,
        ]);

        $p = MrPatient::create([
            'MR_PAT_CODE' => '11111111-2222-3333-4444-555555555555',
            'MR_PAT_ID_NO' => 'PAT-1001',
            'MR_PAT_FULL_NAME' => 'عبدالله المحمد',
            'MR_PAT_GENDER' => 'MALE',
        ]);

        $dept = WcDepartment::first();
        $diag = WcDiagnosis::first();
        $wtype = WcWoundType::first();

        $req = WcRequestReg::create([
            'WC_REQ_CODE' => 'ca4535df-182b-488a-baff-963c2344af90',
            'WC_REQ_PATIENT_CD' => $p->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $p->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $p->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $e->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $u->PER_USER_CODE,
            'WC_REQ_STATUS' => 'APPROVED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $reg = WcRegistration::create([
            'WC_REG_CODE' => 'ca4535df-182b-488a-baff-963c2344af91',
            'WC_REG_REQUEST_CD' => $req->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $p->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $dept->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $e->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $u->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        WcRegDiagnosis::create([
            'WC_RDIAG_CODE' => (string) Str::uuid(),
            'WC_RDIAG_REGISTRATION_CD' => $reg->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diag->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $wtype->WC_WTYPE_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $e->PER_EMP_CODE,
            'WC_RDIAG_ENTRY_USER_CD' => $u->PER_USER_CODE,
        ]);
    }
}
