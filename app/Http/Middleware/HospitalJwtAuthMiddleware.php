<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HospitalJwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return $this->unauthenticatedResponse($request);
        }

        $secret = config('services.hospital_auth.jwt_secret', 'wccms_hospital_jwt_secret_key_minimum_32_bytes!');
        if (strlen($secret) < 32) {
            $secret = str_pad($secret, 32, '0');
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        } catch (Exception $e) {
            return $this->invalidTokenResponse($request);
        }

        $username = $decoded->sub ?? null;

        if (! $username) {
            return $this->invalidTokenResponse($request);
        }

        // Who may open WCCMS is decided by the hospital permission system that issues the token, so a first-time
        // user is provisioned active with full access; there is no approval step and no first admin to seed.
        $user = User::firstOrCreate(
            ['PER_USER_USERNAME' => $username],
            [
                'PER_USER_CODE' => (string) Str::uuid(),
                'PER_USER_ROLE' => 'NURSE',
                'PER_USER_IS_ACTIVE' => true,
            ],
        );

        if (! $user->PER_USER_IS_ACTIVE) {
            return $this->forbiddenResponse($request, 'access_disabled', 'تم إيقاف صلاحية الوصول لنظام العيادة، يرجى مراجعة المسؤول');
        }

        Auth::login($user);

        return $next($request);
    }

    /**
     * Extract JWT token from Authorization header or cookie.
     */
    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if ($header && preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return $request->cookie('hospital_token') ?? $request->cookies->get('hospital_token');
    }

    /**
     * Unauthenticated response (missing token).
     */
    private function unauthenticatedResponse(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'جلسة العمل غير صالحة، يرجى تسجيل الدخول عبر نظام المستشفى',
            ], 401);
        }

        $loginUrl = config('services.hospital_auth.login_url', '/mock-hospital-auth');

        return redirect()->away($loginUrl);
    }

    /**
     * Invalid token response.
     */
    private function invalidTokenResponse(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'invalid_token',
                'message' => 'رمز التحقق غير صالح',
            ], 401);
        }

        $loginUrl = config('services.hospital_auth.login_url', '/mock-hospital-auth');

        return redirect()->away($loginUrl);
    }

    /**
     * Forbidden response (pending approval or deactivated).
     */
    private function forbiddenResponse(Request $request, string $error, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ], 403);
        }

        return response()->view('errors.403', ['message' => $message], 403);
    }
}
