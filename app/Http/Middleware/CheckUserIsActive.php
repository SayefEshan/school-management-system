<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*') && $request->user() && $request->user()->is_active !== true) {
            $request->user()->firebaseTokens()->delete();
            $request->user()->tokens()->delete();
            return apiResponse(false, 'Your account is not active.', 403);
        }

        if ($request->user() && $request->user()->is_active !== true) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['Your account is not active.']);
        }
        return $next($request);
    }
}
