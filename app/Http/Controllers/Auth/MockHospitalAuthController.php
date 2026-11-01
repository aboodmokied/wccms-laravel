<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Support\Hospital;
use App\Support\LocalHospitalCopy;
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

        // 2. Development only: give an active account an employee in the local copy of EMPLOYEE_TB.
        //    The real hospital table (HOSPITAL_SCHEMA set) is never written to.
        if ($isActive && ! Hospital::isExternal() && ! $user->employee) {
            $defaultName = $fullName ?: ($role === 'ADMIN' ? 'د. مشرف العيادة' : 'ممرض '.$username);
            $names = preg_split('/\s+/u', trim($defaultName)) ?: [$defaultName];

            $serial = (int) Employee::query()->max('EMP_SERIAL') + 1;
            $employee = LocalHospitalCopy::firstOrInsert(Employee::class, ['EMP_SERIAL' => $serial], [
                'EMP_FIRST_NAME_AR' => $names[0] ?? null,
                'EMP_FATHER_NAME_AR' => count($names) > 2 ? $names[1] : null,
                'EMP_GRANDFATHER_NAME_AR' => count($names) > 3 ? $names[2] : null,
                'EMP_LAST_NAME_AR' => count($names) > 1 ? implode(' ', array_slice($names, min(count($names) - 1, 3))) : null,
                'EMP_ACTIVE' => 1,
                'EMP_REC_CD' => 1,
            ]);

            $user->update(['PER_USER_EMP_CD' => $employee->EMP_SERIAL, 'PER_USER_HOS_NO' => Hospital::number()]);
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
