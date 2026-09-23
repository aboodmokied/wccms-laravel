<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HospitalJwtAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'secret-key-change-in-production-32bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hospital_auth.jwt_secret' => $this->secret]);
        config(['services.hospital_auth.login_url' => 'http://hospital.local/login']);
    }

    private function generateJwt(string $username, int $expOffset = 3600, ?string $secret = null): string
    {
        $payload = [
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + $expOffset,
            'iss' => 'hospital-auth-system',
        ];

        return JWT::encode($payload, $secret ?? $this->secret, 'HS256');
    }

    public function test_unauthenticated_web_request_redirects_to_hospital_login_url(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('http://hospital.local/login');
    }

    public function test_unauthenticated_json_request_returns_401_with_arabic_message(): void
    {
        $response = $this->getJson('/dashboard');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'unauthenticated',
            'message' => 'جلسة العمل غير صالحة، يرجى تسجيل الدخول عبر نظام المستشفى',
        ]);
    }

    public function test_invalid_jwt_signature_returns_401(): void
    {
        $invalidToken = $this->generateJwt('testuser', 3600, 'wrong-secret-key-that-is-at-least-32-bytes!');

        $response = $this->withHeader('Authorization', 'Bearer '.$invalidToken)
            ->getJson('/dashboard');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'invalid_token',
            'message' => 'رمز التحقق غير صالح',
        ]);
    }

    public function test_expired_jwt_returns_401(): void
    {
        $expiredToken = $this->generateJwt('testuser', -3600);

        $response = $this->withHeader('Authorization', 'Bearer '.$expiredToken)
            ->getJson('/dashboard');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'invalid_token',
            'message' => 'رمز التحقق غير صالح',
        ]);
    }

    public function test_first_time_user_is_auto_provisioned_with_null_role_and_inactive_status(): void
    {
        $token = $this->generateJwt('new_nurse_01');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/dashboard');

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'pending_approval',
            'message' => 'حسابك قيد المراجعة والاعتماد من قبل مسؤول النظام',
        ]);

        $this->assertDatabaseHas('per_users_tb', [
            'PER_USER_USERNAME' => 'new_nurse_01',
            'PER_USER_ROLE' => null,
            'PER_USER_IS_ACTIVE' => 0,
        ]);
    }

    public function test_deactivated_account_returns_403_access_disabled(): void
    {
        $user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'deactivated_user',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 0,
        ]);

        $token = $this->generateJwt('deactivated_user');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/dashboard');

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'access_disabled',
            'message' => 'تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول',
        ]);
    }

    public function test_valid_active_user_jwt_authenticates_successfully(): void
    {
        $user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'active_nurse',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('active_nurse');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/dashboard');

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    public function test_jwt_via_cookie_authenticates_successfully(): void
    {
        $user = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'cookie_user',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('cookie_user');

        $response = $this->call('GET', '/dashboard', [], ['hospital_token' => $token], [], ['HTTP_ACCEPT' => 'application/json']);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }
}
