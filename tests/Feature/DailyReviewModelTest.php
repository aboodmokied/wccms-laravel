<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\PerUser;
use App\Models\WcDailyReview;
use App\Models\WcDailyReviewDetail;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcScheduledReview;
use App\Models\WcWoundType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DailyReviewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_review_and_scheduled_review_tracking(): void
    {
        // 1. Setup User, Employee, Patient, Dept, Reg
        $user = PerUser::create([
            'PER_USER_USERNAME' => 'test_user',
            'PER_USER_PASSWORD' => bcrypt('pass'),
            'PER_USER_ROLE' => 'NURSE',
        ]);

        $nurse = Employee::factory()->named('Nurse Layla')->create();
        $patient = MrPatient::factory()->male()->named('Omar H.')->create([
            'MRP_ID' => 500100101,
        ]);
        $dept = WcDepartment::create(['WC_DEPT_NAME' => 'Surgery Clinic']);

        $request = WcRequestReg::create([
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'Omar H.',
            'WC_REQ_PAT_ID_SNAPSHOT' => 'P-999',
            'WC_REQ_NURSE_CD' => $nurse->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'APPROVED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $reg = WcRegistration::create([
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $dept->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $nurse->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);

        $diag = WcDiagnosis::create(['WC_DIAG_NAME' => 'Venous Ulcer']);
        $wtype = WcWoundType::create(['WC_WTYPE_NAME' => 'Leg Ulcer']);

        $regDiag = WcRegDiagnosis::create([
            'WC_RDIAG_REGISTRATION_CD' => $reg->WC_REG_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $diag->WC_DIAG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $wtype->WC_WTYPE_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $nurse->EMP_SERIAL,
            'WC_RDIAG_ENTRY_USER_CD' => $user->PER_USER_CODE,
        ]);

        // 2. Schedule Review Appointment
        $scheduled = WcScheduledReview::create([
            'WC_SREV_REGISTRATION_CD' => $reg->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(3),
            'WC_SREV_VISIT_SERIAL' => 1,
            'WC_SREV_STATUS' => 'PENDING',
        ]);

        $this->assertEquals(1, $scheduled->WC_SREV_VISIT_SERIAL);
        $this->assertEquals('PENDING', $scheduled->WC_SREV_STATUS);

        // 3. Create Daily Review Visit Header & Details
        $dressing = WcDressing::create(['WC_DRESS_NAME' => 'Foam Dressing']);
        $med = WcMedication::create(['WC_MED_NAME' => 'Saline Wash']);

        $dailyReview = WcDailyReview::create([
            'WC_DREV_REGISTRATION_CD' => $reg->WC_REG_CODE,
            'WC_DREV_SCHEDULED_REVIEW_CD' => $scheduled->WC_SREV_CODE,
            'WC_DREV_VISIT_DATE' => now(),
            'WC_DREV_NURSE_CD' => $nurse->EMP_SERIAL,
            'WC_DREV_ENTRY_USER_CD' => $user->PER_USER_CODE,
            'WC_DREV_OVERALL_STATUS' => 'WORSENED_STAY',
            'WC_DREV_GENERAL_NOTES' => 'Patient visited for follow-up review',
        ]);

        $detail = WcDailyReviewDetail::create([
            'WC_REVD_CODE' => (string) Str::uuid(),
            'WC_REVD_REVIEW_CD' => $dailyReview->WC_DREV_CODE,
            'WC_REVD_REVIEW_TYPE' => 'DAILY',
            'WC_DREVD_REG_DIAGNOSIS_CD' => $regDiag->WC_RDIAG_CODE,
            'WC_DREVD_DRESSING_CD' => $dressing->WC_DRESS_CODE,
            'WC_DREVD_MEDICATION_CD' => $med->WC_MED_CODE,
            'WC_DREVD_WOUND_DESCRIPTION' => 'Granulation tissue forming well',
            'WC_DREVD_STATUS' => 'IMPROVED_FOLLOWUP',
        ]);

        // Complete appointment
        $scheduled->update(['WC_SREV_STATUS' => 'COMPLETED']);
        $this->assertEquals('COMPLETED', $scheduled->WC_SREV_STATUS);

        // Verify Voided Status Capability
        $voidedAppointment = WcScheduledReview::create([
            'WC_SREV_REGISTRATION_CD' => $reg->WC_REG_CODE,
            'WC_SREV_SCHEDULED_DATE' => now()->addDays(7),
            'WC_SREV_VISIT_SERIAL' => 2,
            'WC_SREV_STATUS' => 'VOIDED',
        ]);

        $this->assertEquals('VOIDED', $voidedAppointment->WC_SREV_STATUS);
    }
}
