<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'تم تسجيل الخروج بنجاح',
            ]);
        }

        $loginUrl = config('services.hospital_auth.login_url', 'http://hospital.local/login');

        return redirect()->away($loginUrl);
    }
}
