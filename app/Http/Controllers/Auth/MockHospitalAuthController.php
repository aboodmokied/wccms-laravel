<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PerEmployee;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class MockHospitalAuthController extends Controller
{
    public function index()
    {
        return view('auth.mock_login');
    }

    public function login(Request $request)
    {
        $username = trim($request->input('username', 'ward_nurse_demo'));
        $roleInput = $request->input('role', 'NURSE'); // NURSE, ADMIN, PENDING
        $fullName = $request->input('full_name');

        if (! $username) {
            return redirect()->back()->with('error', 'يرجى إدخال اسم المستخدم');
        }

        $isPending = ($roleInput === 'PENDING');
        $role = $isPending ? null : $roleInput;
        $isActive = $isPending ? 0 : 1;

        // 1. Find or create User
        $user = User::where('PER_USER_USERNAME', $username)->first();

        if (! $user) {
            $user = User::create([
                'PER_USER_CODE' => (string) Str::uuid(),
                'PER_USER_USERNAME' => $username,
                'PER_USER_ROLE' => $role,
                'PER_USER_IS_ACTIVE' => $isActive,
            ]);
        } else {
            $user->update([
                'PER_USER_ROLE' => $role,
                'PER_USER_IS_ACTIVE' => $isActive,
            ]);
        }

        // 2. Ensure Employee directory record exists if user is active
        if ($isActive) {
            $employee = PerEmployee::where('PER_EMP_USER_CD', $user->PER_USER_CODE)->first();

            if (! $employee) {
                $defaultTitle = $role === 'ADMIN' ? 'مشرف سريري' : 'ممرض قانوني';
                $defaultName = $fullName ?: ($role === 'ADMIN' ? 'د. مشرف العيادة' : 'ممرض '.$username);

                PerEmployee::create([
                    'PER_EMP_CODE' => (string) Str::uuid(),
                    'PER_EMP_FULL_NAME' => $defaultName,
                    'PER_EMP_JOB_TITLE' => $defaultTitle,
                    'PER_EMP_USER_CD' => $user->PER_USER_CODE,
                ]);
            }
        }

        // 3. Issue Hospital SSO JWT Token
        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');
        if (strlen($secret) < 32) {
            $secret = str_pad($secret, 32, '0');
        }

        $payload = [
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + (86400 * 7), // 7 days
            'iss' => 'hospital-auth-system-mock',
        ];

        $jwtToken = JWT::encode($payload, $secret, 'HS256');

        // 4. Attach cookie & login
        if ($isActive && $role) {
            Auth::login($user);
        }

        $cookie = Cookie::make('hospital_token', $jwtToken, 60 * 24 * 7, null, null, false, false);

        if ($isPending) {
            return redirect()->route('dashboard')->withCookie($cookie);
        }

        return redirect()->intended(route('dashboard'))
            ->withCookie($cookie)
            ->with('success', "تم تسجيل الدخول بنجاح عبر النظام الموحد للمستشفى بشخصية: {$username}");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $cookie = Cookie::forget('hospital_token');

        return redirect()->route('login')
            ->withCookie($cookie)
            ->with('success', 'تم تسجيل الخروج بنجاح من نظام المستشفى الموحد');
    }
}
