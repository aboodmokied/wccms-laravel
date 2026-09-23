<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (! $user || ! $user->PER_USER_IS_ACTIVE || ! in_array($user->PER_USER_ROLE, $roles, true)) {
            $message = 'غير مصرح لك بالوصول إلى هذه الصفحة';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => $message,
                ], 403);
            }

            return response()->view('errors.403', ['message' => $message], 403);
        }

        return $next($request);
    }
}
