<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user is not authenticated, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Bu sayfayı görüntülemek için giriş yapmalısınız.');
        }

        $user = Auth::user();

        // If user is not active, log them out
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Hesabınız aktif değil. Lütfen yöneticinizle iletişime geçin.');
        }

        if ($user->level !== User::LEVEL_ADMIN) {
            abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }

        return $next($request);
    }
}
