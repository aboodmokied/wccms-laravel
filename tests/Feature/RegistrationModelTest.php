<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\PerUser;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcProcedure;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_request_and_clinical_registration_lifecycle(): void
    {
        // 1. Create Users & Employees
        $entryUser = PerUser::create([
            'PER_USER_USERNAME' => 'entry_nurse',
            'PER_USER_PASSWORD' => bcrypt('password'),
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $nurseEmp = PerEmployee::create([
            'PER_EMP_FULL_NAME' => 'Nurse Sarah',
            'PER_EMP_JOB_TITLE' => 'Staff Nurse',
            'PER_EMP_USER_CD' => $entryUser->PER_USER_CODE,
        ]);

        $supervisorEmp = PerEmployee::create([
            'PER_EMP_FULL_NAME' => 'Supervisor Dr. Ahmed',
            'PER_EMP_JOB_TITLE' => 'Clinical Supervisor',
        ]);

        // 2. Create Patient
        $patient = MrPatient::create([
            'MR_PAT_ID_NO' => 'P-100200',
            'MR_PAT_FULL_NAME' => 'Khaled Ali',
            'MR_PAT_PHONE' => '0599123456',
            'MR_PAT_GENDER' => 'MALE',
        ]);

        // 3. Submit Registration Request
        $request = WcRequestReg::create([
            'WC_REQ_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->MR_PAT_FULL_NAME,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MR_PAT_ID_NO,
            'WC_REQ_PAT_PHONE_SNAPSHOT' => $patient->MR_PAT_PHONE,
            'WC_REQ_NURSE_CD' => $nurseEmp->PER_EMP_CODE,
            'WC_REQ_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $this->assertEquals('PENDING', $request->WC_REQ_STATUS);

        // 4. Approve Request
        $request->update([
            'WC_REQ_STATUS' => 'APPROVED',
            'WC_REQ_REVIEWED_BY_CD' => $supervisorEmp->PER_EMP_CODE,
            'WC_REQ_NOTES' => 'Approved for wound management clinic',
        ]);

        $this->assertEquals('APPROVED', $request->WC_REQ_STATUS);
        $this->assertEquals($supervisorEmp->PER_EMP_CODE, $request->WC_REQ_REVIEWED_BY_CD);

        // 5. Create Clinical Registration
        $dept = WcDepartment::create(['WC_DEPT_NAME' => 'Wound Clinic']);
        $proc = WcProcedure::create(['WC_PROC_NAME' => 'Debridement']);

        $registration = WcRegistration::create([
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $dept->WC_DEPT_CODE,
            'WC_REG_PROCEDURE_CD' => $proc->WC_PROC_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $nurseEmp->PER_EMP_CODE,
            'WC_REG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $this->assertEquals('ACTIVE', $registration->WC_REG_STATUS);

        // 6. Attach Diagnosis
        $diag = WcDiagnosis::create(['WC_DIAG_NAME' => 'Diabetic Ulcer']);
        $wtype = WcWoundType::create(['WC_WTYPE_NAME' => 'Chronic Lesion']);

        $regDiag = WcRegDiagnosis::create([
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diag->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $wtype->WC_WTYPE_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $nurseEmp->PER_EMP_CODE,
            'WC_RDIAG_ENTRY_USER_CD' => $entryUser->PER_USER_CODE,
        ]);

        $this->assertDatabaseHas('wc_reg_diagnosis_tb', [
            'WC_RDIAG_CODE' => $regDiag->WC_RDIAG_CODE,
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE,
        ]);
    }
}
