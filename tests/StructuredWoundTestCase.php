<?php

namespace Tests;

use App\Models\MrPatient;
use App\Models\PerEmployee;
use App\Models\User;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcRegDiagnosis;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class StructuredWoundTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected PerEmployee $employee;

    protected MrPatient $patient;

    protected WcDepartment $department;

    protected WcWoundType $woundType;

    protected WcDiagnosis $diagnosis;

    protected WcDressing $dressing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WcAssessmentChoiceSeeder::class);
        $this->user = User::create(['PER_USER_CODE' => (string) Str::uuid(), 'PER_USER_USERNAME' => 'structured_user', 'PER_USER_ROLE' => 'NURSE', 'PER_USER_IS_ACTIVE' => 1]);
        $this->employee = PerEmployee::create(['PER_EMP_CODE' => (string) Str::uuid(), 'PER_EMP_FULL_NAME' => 'ممرض منظم', 'PER_EMP_USER_CD' => $this->user->PER_USER_CODE]);
        $this->patient = MrPatient::create(['MR_PAT_CODE' => (string) Str::uuid(), 'MR_PAT_ID_NO' => '99112233', 'MR_PAT_FULL_NAME' => 'مريض التقييم المنظم', 'MR_PAT_GENDER' => 'MALE']);
        $this->department = WcDepartment::create(['WC_DEPT_CODE' => (string) Str::uuid(), 'WC_DEPT_NAME' => 'عيادة الجروح', 'WC_DEPT_IS_ACTIVE' => 1]);
        $this->woundType = WcWoundType::create(['WC_WTYPE_CODE' => (string) Str::uuid(), 'WC_WTYPE_NAME' => 'قرحة', 'WC_WTYPE_IS_ACTIVE' => 1]);
        $this->diagnosis = WcDiagnosis::create(['WC_DIAG_CODE' => (string) Str::uuid(), 'WC_DIAG_NAME' => 'قرحة ضغط', 'WC_DIAG_IS_ACTIVE' => 1]);
        $this->dressing = WcDressing::create(['WC_DRESS_CODE' => (string) Str::uuid(), 'WC_DRESS_NAME' => 'ضماد رغوي', 'WC_DRESS_IS_ACTIVE' => 1]);
    }

    protected function pendingRequest(): WcRequestReg
    {
        return WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(), 'WC_REQ_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $this->patient->MR_PAT_FULL_NAME, 'WC_REQ_PAT_ID_SNAPSHOT' => $this->patient->MR_PAT_ID_NO,
            'WC_REQ_NURSE_CD' => $this->employee->PER_EMP_CODE, 'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING', 'WC_REQ_CREATED_AT' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    protected function registrationPayload(): array
    {
        return [
            'department_code' => $this->department->WC_DEPT_CODE, 'clinic_nurse_code' => $this->employee->PER_EMP_CODE,
            'entry_user_code' => $this->user->PER_USER_CODE, 'assessed_at' => now()->toDateTimeString(),
            'has_allergies' => false, 'allergies_description' => null, 'no_comorbidities' => true, 'pmh_codes' => [],
            'next_visit_date' => now()->addWeek()->toDateString(), 'follow_up_frequency' => null, 'follow_up_objective' => 'متابعة الالتئام',
            'diagnoses' => [$this->initialAssessment()],
        ];
    }

    /** @return array<string, mixed> */
    protected function initialAssessment(): array
    {
        return [
            'wound_type_code' => $this->woundType->WC_WTYPE_CODE, 'diagnosis_code' => $this->diagnosis->WC_DIAG_CODE,
            'wound_status_choice_code' => $this->choice('WOUND_STATUS', 'FIRST_VISIT'),
            'wound_bed_choice_codes' => [$this->choice('WOUND_BED', 'GRANULATION')],
            'wound_margin_choice_codes' => [$this->choice('WOUND_MARGIN', 'NONE_OBSERVED')],
            'exudate_amount_choice_code' => $this->choice('EXUDATE_AMOUNT', 'DRY'), 'exudate_type_choice_code' => null,
            'odor_choice_code' => $this->choice('ODOR', 'NONE'), 'pain_severity_choice_code' => $this->choice('PAIN_SEVERITY', 'NONE'),
            'pain_at_dressing' => false, 'fever_choice_code' => $this->choice('FEVER', 'NONE'),
            'length_mm' => 20.25, 'width_mm' => 10, 'depth_mm' => 0,
            'location_choice_code' => $this->choice('LOCATION', 'FOOT'), 'laterality_choice_code' => $this->choice('LATERALITY', 'LEFT'),
            'swab_taken' => false, 'nutrition_choice_code' => $this->choice('NUTRITION', 'WELL_NOURISHED'),
            'dressing_code' => $this->dressing->WC_DRESS_CODE, 'treatment_notes' => null, 'photos' => [],
        ];
    }

    /** @return array<string, mixed> */
    protected function followupAssessment(WcRegDiagnosis $wound): array
    {
        return [
            'registration_diagnosis_code' => $wound->WC_RDIAG_CODE,
            'wound_status_choice_code' => $this->choice('WOUND_STATUS', 'IMPROVING'),
            'wound_bed_choice_codes' => [$this->choice('WOUND_BED', 'GRANULATION')], 'wound_margin_choice_codes' => [],
            'exudate_amount_choice_code' => $this->choice('EXUDATE_AMOUNT', 'DRY'), 'exudate_type_choice_code' => null,
            'odor_choice_code' => $this->choice('ODOR', 'NONE'), 'pain_severity_choice_code' => $this->choice('PAIN_SEVERITY', 'NONE'),
            'pain_at_dressing' => false, 'dressing_code' => $this->dressing->WC_DRESS_CODE,
            'clinical_observation' => 'نسيج حبيبي جيد', 'wound_changes' => 'تحسن واضح',
            'outcome_status' => 'IMPROVED_FOLLOWUP', 'follow_up_required' => true, 'photos' => [],
        ];
    }

    /** @return array{0: WcRegistration, 1: WcRegDiagnosis} */
    protected function registrationWithWound(string $status = 'ACTIVE'): array
    {
        $request = $this->pendingRequest();
        $request->update(['WC_REQ_STATUS' => 'ACCEPTED']);
        $registration = WcRegistration::create([
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE, 'WC_REG_PATIENT_CD' => $this->patient->MR_PAT_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE, 'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->employee->PER_EMP_CODE, 'WC_REG_ENTRY_USER_CD' => $this->user->PER_USER_CODE, 'WC_REG_STATUS' => $status,
        ]);
        $wound = WcRegDiagnosis::create([
            'WC_RDIAG_REGISTRATION_CD' => $registration->WC_REG_CODE, 'WC_RDIAG_WOUND_TYPE_CD' => $this->woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $this->diagnosis->WC_DIAG_CODE, 'WC_RDIAG_CLINICAL_EMP_CD' => $this->employee->PER_EMP_CODE,
            'WC_RDIAG_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
        ]);

        return [$registration, $wound];
    }

    protected function choice(string $category, string $key): string
    {
        return WcAssessmentChoice::query()->where('WC_ACH_CATEGORY', $category)->where('WC_ACH_KEY', $key)->valueOrFail('WC_ACH_CODE');
    }
}
