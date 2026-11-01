<?php

namespace Tests\Feature;

use App\Models\MrPatient;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportsAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private string $adminJwtToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'admin_tester',
            'PER_USER_ROLE' => 'ADMIN',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');

        $this->adminJwtToken = JWT::encode([
            'sub' => $this->adminUser->PER_USER_USERNAME,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $secret, 'HS256');
    }

    public function test_dashboard_renders_live_kpis_and_recent_activities(): void
    {
        $response = $this->call(
            'GET',
            '/dashboard',
            [],
            ['hospital_token' => $this->adminJwtToken]
        );

        $response->assertStatus(200);
        $response->assertSee('لوحة التحليلات والمؤشرات التنفيذية');
        $response->assertSee('المرضى المسجلون حالياً');
        $response->assertSee('متوسط مدة العلاج');
        $response->assertSee('نسبة التخلف عن المواعيد');
    }

    public function test_dashboard_period_filtering_query_params(): void
    {
        $response = $this->call(
            'GET',
            '/dashboard',
            ['period' => '30days'],
            ['hospital_token' => $this->adminJwtToken]
        );

        $response->assertStatus(200);
        $response->assertSee('آخر 30 يومًا');
    }

    public function test_patient_history_search_and_render_timeline(): void
    {
        // A patient of the configured hospital in MR_PATIENT_TB, searched by medical record number
        $patientCode = MrPatient::factory()->male()->named('أحمد', null, null, 'الغامدي')->create([
            'MRP_ID' => 102938475,
            'MRP_MOBILE_NO' => '0501234567',
        ])->MRP_PATREC_CODE;

        $response = $this->call(
            'GET',
            '/reports/history',
            ['search' => $patientCode],
            ['hospital_token' => $this->adminJwtToken]
        );

        $response->assertStatus(200);
        $response->assertSee('أحمد الغامدي');
        $response->assertSee('102938475');
    }

    public function test_patient_history_csv_export_stream(): void
    {
        $patientCode = MrPatient::factory()->female()->named('فاطمة', null, null, 'العتيبي')->create([
            'MRP_ID' => 998877665,
            'MRP_MOBILE_NO' => '0559988776',
        ])->MRP_PATREC_CODE;

        $response = $this->call(
            'GET',
            "/reports/history/{$patientCode}/export",
            [],
            ['hospital_token' => $this->adminJwtToken]
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="patient-'.$patientCode.'-history.csv"');
    }
}
