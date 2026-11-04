<?php

namespace Tests\Feature;

use App\Models\AdmissionPatient;
use App\Models\Employee;
use App\Models\HospitalLocation;
use App\Models\InpatientStay;
use App\Models\MrPatient;
use App\Models\User;
use App\Models\WcRequestReg;
use App\Support\SystemUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WardReferralSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Employee $employee;

    private MrPatient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'ward_nurse_1',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->employee = Employee::factory()->named('ممرض قسم الجراحة')->create();

        $this->user->update(['PER_USER_EMP_CD' => $this->employee->EMP_SERIAL]);

        $doctor = Employee::factory()->named('د. سامي الخطيب')->create();

        $this->patient = MrPatient::factory()->male()->named('أحمد علي حسن')->create([
            'MRP_ID' => 401234567,
            'MRP_MOBILE_NO' => '0599123456',
            'MRP_DOB' => '1985-04-12',
        ]);

        // The open hospital admission (ADMISSION_PATIENT_TB + a not-cancelled INPATIENT_ADM_TB stay) gives the ward
        // and the attending doctor.
        $ward = HospitalLocation::factory()->create(['LOC_NAME_AR' => 'الجراحة العامة']);
        $admission = AdmissionPatient::factory()->create([
            'ADMISSION_PATREC_CD' => $this->patient->MRP_PATREC_CODE,
            'ADMISSION_LOC_CD' => $ward->LOC_CODE,
            'ADMISSION_ENTER_DOC_CD' => $doctor->EMP_SERIAL,
        ]);
        InpatientStay::factory()->create([
            'INPATIENT_ADMISSION_CD' => $admission->ADMISSION_CODE,
            'INPATIENT_SUP_LOC_CD' => $ward->LOC_CODE,
        ]);
    }

    public function test_ward_nurse_can_search_patient_and_receive_admission_details(): void
    {
        $response = $this->getJson(route('requests.search_patient', ['query' => '401234567']));

        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'patient' => [
                'code' => $this->patient->MRP_PATREC_CODE,
                'id_number' => '401234567',
                'full_name' => 'أحمد علي حسن',
            ],
            'admission' => ['attending_doctor' => 'د. سامي الخطيب', 'ward_name' => 'الجراحة العامة'],
            'has_pending_request' => false,
            'has_active_registration' => false,
        ]);
    }

    public function test_searching_unknown_id_number_returns_not_found_without_creating_patient(): void
    {
        $patientCountBefore = MrPatient::count();

        $response = $this->getJson(route('requests.search_patient', ['query' => '000000000']));

        $response->assertNotFound();
        $response->assertJson(['found' => false]);
        $this->assertSame($patientCountBefore, MrPatient::count());
        $this->assertDatabaseMissing(MrPatient::class, ['MRP_ID' => 0]);
    }

    public function test_ward_nurse_can_submit_referral_request_with_snapshot_data(): void
    {
        $response = $this->post(route('requests.store'), [
            'patient_code' => $this->patient->MRP_PATREC_CODE,
            'attending_nurse_code' => $this->employee->EMP_SERIAL,
            'notes' => 'جرح ما بعد العملية الجراحية يحتاج تقييم العيادة',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('requests.my'));

        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'أحمد علي حسن',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => SystemUser::CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_NOTES' => 'جرح ما بعد العملية الجراحية يحتاج تقييم العيادة',
        ]);
    }

    public function test_referral_for_patient_without_id_number_or_name_stores_null_snapshots(): void
    {
        // HOS.MR_PATIENT_TB allows MRP_ID and every name column to be NULL; on Oracle '' is NULL too.
        $patient = MrPatient::factory()->named(null)->create(['MRP_ID' => null]);

        $response = $this->post(route('requests.store'), [
            'patient_code' => $patient->MRP_PATREC_CODE,
            'attending_nurse_code' => $this->employee->EMP_SERIAL,
        ]);

        $response->assertRedirect(route('requests.my'));
        $this->assertDatabaseHas('wc_request_reg_tb', [
            'WC_REQ_PATIENT_CD' => $patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => null,
            'WC_REQ_PAT_ID_SNAPSHOT' => null,
        ]);
    }

    public function test_duplicate_referral_submission_is_prevented(): void
    {
        WcRequestReg::create([
            'WC_REQ_CODE' => (string) Str::uuid(),
            'WC_REQ_PATIENT_CD' => $this->patient->MRP_PATREC_CODE,
            'WC_REQ_PAT_NAME_SNAPSHOT' => 'أحمد علي حسن',
            'WC_REQ_PAT_ID_SNAPSHOT' => '401234567',
            'WC_REQ_NURSE_CD' => $this->employee->EMP_SERIAL,
            'WC_REQ_ENTRY_USER_CD' => $this->user->PER_USER_CODE,
            'WC_REQ_STATUS' => 'PENDING',
            'WC_REQ_CREATED_AT' => now(),
        ]);

        $response = $this->post(route('requests.store'), [
            'patient_code' => $this->patient->MRP_PATREC_CODE,
            'attending_nurse_code' => $this->employee->EMP_SERIAL,
            'notes' => 'طلب مكرر',
        ]);

        $response->assertSessionHasErrors(['patient_code']);
    }
}
