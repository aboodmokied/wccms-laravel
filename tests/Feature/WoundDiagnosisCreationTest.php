<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcAssessmentChoice;
use App\Models\WcDepartment;
use App\Models\WcDiagnosis;
use App\Models\WcDressing;
use App\Models\WcMedication;
use App\Models\WcRegistration;
use App\Models\WcRequestReg;
use App\Models\WcWoundType;
use App\Support\SystemUser;
use Database\Seeders\WcAssessmentChoiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WoundDiagnosisCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $nurseUser;

    private Employee $nurseEmployee;

    private WcDepartment $department;

    private WcRegistration $registration;

    private WcWoundType $woundType;

    private WcDiagnosis $diagnosis;

    private WcDressing $dressing;

    private WcMedication $medication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WcAssessmentChoiceSeeder::class);

        $this->nurseUser = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'test_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->nurseEmployee = Employee::factory()->named('ممرض فحص تجريبي')->create();

        $this->nurseUser->update(['PER_USER_EMP_CD' => $this->nurseEmployee->EMP_SERIAL]);

        $this->department = WcDepartment::create([
            'WC_DEPT_CODE' => (string) Str::uuid(),
            'WC_DEPT_NAME' => 'قسم الجراحة',
            'WC_DEPT_IS_ACTIVE' => 1,
        ]);

        $this->woundType = WcWoundType::create([
            'WC_WTYPE_CODE' => (string) Str::uuid(),
            'WC_WTYPE_NAME' => 'قدم سكرية (Diabetic Foot)',
            'WC_WTYPE_IS_ACTIVE' => 1,
        ]);

        $this->diagnosis = WcDiagnosis::create([
            'WC_DIAG_CODE' => (string) Str::uuid(),
            'WC_DIAG_NAME' => 'قرحة القدم السكرية الدرجة الثانية',
            'WC_DIAG_IS_ACTIVE' => 1,
        ]);

        $this->dressing = WcDressing::create([
            'WC_DRESS_CODE' => (string) Str::uuid(),
            'WC_DRESS_NAME' => 'ضمادة رغوية (Foam Dressing)',
            'WC_DRESS_IS_ACTIVE' => 1,
        ]);

        $this->medication = WcMedication::create([
            'WC_MED_CODE' => (string) Str::uuid(),
            'WC_MED_NAME' => 'مرهم سيلفر سلفاديازين (Silver Sulfadiazine)',
            'WC_MED_IS_ACTIVE' => 1,
        ]);

        $patient = MrPatient::factory()->male()->named('أحمد محمود علي')->create([
            'MRP_ID' => 100200300,
        ]);

        $request = WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => $patient->full_name,
            'WC_REQ_PAT_ID_SNAPSHOT' => $patient->MRP_ID,
            'WC_REQ_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REQ_STATUS' => 'ACCEPTED',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $this->registration = WcRegistration::create([
            'WC_REG_CODE' => (string) Str::uuid(),
            'WC_REG_REQUEST_CD' => $request->WC_REQ_CODE,
            'WC_REG_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REG_DEPARTMENT_CD' => $this->department->WC_DEPT_CODE,
            'WC_REG_DATE' => now(),
            'WC_REG_NURSE_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_REG_ENTRY_USER_CD' => $this->nurseUser->PER_USER_CODE,
            'WC_REG_STATUS' => 'ACTIVE',
        ]);
    }

    public function test_can_add_wound_diagnosis_with_dual_audit_tracking(): void
    {
        $response = $this->call('POST', "/registrations/{$this->registration->WC_REG_CODE}/diagnoses", [
            'clinical_nurse_code' => $this->nurseEmployee->EMP_SERIAL,
            'assessment' => [
                ...$this->structuredBaseline(),
                'wound_type_code' => $this->woundType->WC_WTYPE_CODE,
                'diagnosis_code' => $this->diagnosis->WC_DIAG_CODE,
                'dressing_code' => $this->dressing->WC_DRESS_CODE,
                'medication_code' => $this->medication->WC_MED_CODE,
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect();

        $this->assertDatabaseHas('wc_reg_diagnosis_tb', [
            'WC_RDIAG_REGISTRATION_CD' => $this->registration->WC_REG_CODE,
            'WC_RDIAG_WOUND_TYPE_CD' => $this->woundType->WC_WTYPE_CODE,
            'WC_RDIAG_DIAGNOSIS_CD' => $this->diagnosis->WC_DIAG_CODE,
            'WC_RDIAG_CLINICAL_EMP_CD' => $this->nurseEmployee->EMP_SERIAL,
            'WC_RDIAG_ENTRY_USER_CD' => SystemUser::CODE,
        ]);
    }

    /** @return array<string, mixed> */
    private function structuredBaseline(): array
    {
        return [
            'wound_status_choice_code' => $this->choice('WOUND_STATUS', 'FIRST_VISIT'),
            'wound_bed_choice_codes' => [$this->choice('WOUND_BED', 'GRANULATION')],
            'wound_margin_choice_codes' => [$this->choice('WOUND_MARGIN', 'NONE_OBSERVED')],
            'exudate_amount_choice_code' => $this->choice('EXUDATE_AMOUNT', 'DRY'),
            'exudate_type_choice_code' => null,
            'odor_choice_code' => $this->choice('ODOR', 'NONE'),
            'pain_severity_choice_code' => $this->choice('PAIN_SEVERITY', 'NONE'),
            'pain_at_dressing' => false,
            'fever_choice_code' => $this->choice('FEVER', 'NONE'),
            'length_mm' => 20.25,
            'width_mm' => 10,
            'depth_mm' => 0,
            'location_choice_code' => $this->choice('LOCATION', 'FOOT'),
            'laterality_choice_code' => $this->choice('LATERALITY', 'LEFT'),
            'swab_taken' => false,
            'nutrition_choice_code' => $this->choice('NUTRITION', 'WELL_NOURISHED'),
        ];
    }

    private function choice(string $category, string $key): string
    {
        return WcAssessmentChoice::query()
            ->where('WC_ACH_CATEGORY', $category)
            ->where('WC_ACH_KEY', $key)
            ->valueOrFail('WC_ACH_CODE');
    }
}
