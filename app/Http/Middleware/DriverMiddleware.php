<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DriverMiddleware
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
        
        if ($user->level !== 2) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Hesabınız aktif değildir.');
        }

        return $next($request);
    }
} 