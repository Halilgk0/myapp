<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hesabınız pasif olduğundan oturum sonlandırıldı.');
        }

        if ($user->level !== User::LEVEL_AGENCY) {
            $message = 'Bu sayfaya sadece Sokak Acentası kullanıcıları erişebilir.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            $redirectRoute = $user->isAdmin() ? 'admin.dashboard' : 'home';

            return redirect()->route($redirectRoute)->with('error', $message);
        }

        return $next($request);
    }
}


