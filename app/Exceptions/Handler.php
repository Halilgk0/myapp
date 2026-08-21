<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // CSRF Token mismatch (Page Expired) hatası için özel işlem
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->hasSession()) {
                $request->session()->regenerateToken();
            }

            // AJAX istekleri için JSON yanıt
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Oturum süresi doldu. Lütfen sayfayı yenileyip tekrar deneyin.',
                    'token' => csrf_token()
                ], 419);
            }
            
            // Login sayfası için özel mesaj
            if ($request->is('login') || $request->routeIs('login')) {
                return redirect()->route('login')
                    ->with('error', 'Oturum süresi doldu. Lütfen tekrar giriş yapın.');
            }
            
            // Diğer sayfalar için geri dön ve mesaj göster
            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->with('error', 'İşlem zaman aşımına uğradı. Lütfen tekrar deneyin.');
        });
    }
}
