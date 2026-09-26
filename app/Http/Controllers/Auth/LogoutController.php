<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends Controller
{
    /**
     * Terminate the local application session.
     */
    public function __invoke(Request $request)
    {
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $cookie = Cookie::forget('hospital_token');

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'تم تسجيل الخروج بنجاح',
            ])->withCookie($cookie);
        }

        $loginUrl = config('services.hospital_auth.login_url', route('mock_hospital_auth.index'));

        if (str_starts_with($loginUrl, '/')) {
            return redirect($loginUrl)->withCookie($cookie);
        }

        return redirect()->away($loginUrl)->withCookie($cookie);
    }
}
