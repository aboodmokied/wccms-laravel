<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacAuthorizationTest extends TestCase
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

    public function test_nurse_role_can_access_admin_routes_json(): void
    {
        $nurse = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'nurse_john',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('nurse_john');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/admin');

        $response->assertOk();
    }

    public function test_nurse_role_can_access_admin_routes_web(): void
    {
        $nurse = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'nurse_jane',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('nurse_jane');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/admin');

        $response->assertOk();
    }

    public function test_admin_role_can_access_admin_routes(): void
    {
        $admin = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'admin_super',
            'PER_USER_ROLE' => 'ADMIN',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('admin_super');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/admin');

        $response->assertStatus(200);
    }

    public function test_nurse_role_can_access_clinical_routes(): void
    {
        $nurse = User::create([
            'PER_USER_CODE' => (string) Str::uuid(),
            'PER_USER_USERNAME' => 'nurse_clinical',
            'PER_USER_ROLE' => 'NURSE',
            'PER_USER_IS_ACTIVE' => 1,
        ]);

        $token = $this->generateJwt('nurse_clinical');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/dashboard')
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/requests')
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/reviews/daily/pending')
            ->assertOk();
    }
}
