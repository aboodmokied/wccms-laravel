<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hospital_auth.jwt_secret' => $this->secret]);
        config(['services.hospital_auth.login_url' => 'http://hospital.local/login']);
    }

    private function generateJwt(string $username): string
    {
        $payload = [
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'hospital-auth-system',
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function test_linked_employee_full_name_is_retrieved_for_user(): void
    {
        $userCode = (string) Str::uuid();
        $user = User::create([
            'PER_USER_CODE' => $userCode,
            'PER_USER_USERNAME' => 'nurse_fatima',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $employee = Employee::factory()->named('أ. فاطمة الزهراني')->create();

        $user->update(['PER_USER_EMP_CD' => $employee->EMP_SERIAL]);

        $token = $this->generateJwt('nurse_fatima');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('أ. فاطمة الزهراني');
    }

    public function test_admin_can_view_user_management_list(): void
    {
        $admin = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'admin_user',
            'PER_USER_ROLE' => 'ADMIN',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('admin_user');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_assign_role_and_activate_user(): void
    {
        $admin = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'admin_user',
            'PER_USER_ROLE' => 'ADMIN',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $targetUserCode = (string) Str::uuid();
        $targetUser = User::create([
            'PER_USER_CODE' => $targetUserCode,
            'PER_USER_USERNAME' => 'pending_nurse',
            'PER_USER_ROLE' => null,
            'PER_USER_IS_ACTIVE' => 0,
        ]);

        $employee = Employee::factory()->named('م. أحمد خالد')->create();
        $employeeCode = $employee->EMP_SERIAL;

        $token = $this->generateJwt('admin_user');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/admin/users/{$targetUserCode}", [
                'PER_USER_ROLE' => 'NURSE',
                'PER_USER_IS_ACTIVE' => 1,
                'PER_EMP_CODE' => $employeeCode,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('wc_users_tb', [
            'PER_USER_CODE' => $targetUserCode,
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $this->assertDatabaseHas('wc_users_tb', [
            'PER_USER_CODE' => $targetUserCode,
            'PER_USER_EMP_CD' => $employeeCode,
        ]);
    }

    public function test_deactivating_user_blocks_immediate_subsequent_requests(): void
    {
        $admin = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'admin_user',
            'PER_USER_ROLE' => 'ADMIN',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $targetUserCode = (string) Str::uuid();
        $targetUser = User::create([
            'PER_USER_CODE' => $targetUserCode,
            'PER_USER_USERNAME' => 'nurse_to_block',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $adminToken = $this->generateJwt('admin_user');

        // Deactivate user as Admin
        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->putJson("/admin/users/{$targetUserCode}", [
                'PER_USER_ROLE' => 'NURSE',
                'PER_USER_IS_ACTIVE' => 0,
            ])->assertStatus(200);

        // Nurse attempts request
        $nurseToken = $this->generateJwt('nurse_to_block');

        $response = $this->withHeader('Authorization', 'Bearer '.$nurseToken)
            ->getJson('/dashboard');

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'access_disabled',
            'message' => 'تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول',
        ]);
    }
}
