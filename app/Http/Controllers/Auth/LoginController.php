<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.device-login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Check if this is an API request
        $wantsJson = $request->wantsJson() || $request->is('api/*');
        
        // Validate the login request
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'string'],
        ];
        
        // Only validate recaptcha for web requests
        if (!$wantsJson) {
            $rules['g-recaptcha-response'] = config('recaptcha.enabled') ? 'required|recaptcha' : 'nullable';
        }
        
        $validator = Validator::make($request->all(), $rules, [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre zorunludur.',
            'g-recaptcha-response.required' => 'Lütfen robot olmadığınızı doğrulayın.',
        ]);
        
        if ($validator->fails()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doğrulama hatası',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        // Throttle login attempts
        $throttleKey = strtolower($request->email) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = 'Çok fazla giriş denemesi yaptınız. Lütfen ' . 
                      ceil($seconds / 60) . ' dakika sonra tekrar deneyin.';
            
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 429);
            }
            
            throw ValidationException::withMessages([
                'email' => [$message],
            ])->status(429);
        }

        // Ensure DB connection is available before querying users table
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            Log::error('Database connection failed during login', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sistem şu anda meşgul. Lütfen daha sonra tekrar deneyin.'
                ], 503);
            }

            return back()->withErrors([
                'email' => 'Sistem şu anda meşgul. Lütfen biraz sonra tekrar deneyin.'
            ])->withInput($request->only('email'));
        }

        // Check if the user exists and is active
        $user = User::where('email', $request->email)->first();
        
        // Log login attempt
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => (bool)$user,
        ]);
        
        // Check if user exists and is active
        if ($user && !$user->is_active) {
            Log::warning('Login attempt to inactive account', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);
            
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hesabınız aktif değil. Lütfen yöneticinizle iletişime geçin.'
                ], 403);
            }
            
            return back()->withErrors([
                'email' => 'Hesabınız aktif değil. Lütfen yöneticinizle iletişime geçin.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        $remember = $request->filled('remember');
        $credentials = $request->only('email', 'password');
        
        // Attempt to authenticate the user
        // LoginController.php içinde login metodunun başarılı giriş kısmında
       if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
    
            // Clear login attempts on successful login
            RateLimiter::clear($throttleKey);
    
            // Update last login info
            $user = Auth::user();

            $this->applySessionPartition($request, $user);
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'login_attempts' => 0, // Reset login attempts on successful login
            ]);
    
            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
            
            // Redirect to appropriate dashboard based on user level
            return $this->authenticated($request, $user);
        }
        
        // Increment login attempts on failure
        RateLimiter::hit($throttleKey, 300); // 5 minutes cooldown
        
        // Update user's failed login attempts if user exists
        if ($user) {
            $user->increment('login_attempts');
            
            // Optional: Lock account after too many failed attempts
            $maxAttempts = config('auth.max_login_attempts', 5);
            if ($user->login_attempts >= $maxAttempts) {
                $user->update(['is_active' => false]);
                
                Log::warning('User account locked due to too many failed login attempts', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'attempts' => $user->login_attempts,
                    'ip' => $request->ip(),
                ]);
                
                return back()->withErrors([
                    'email' => 'Çok fazla başarısız giriş denemesi. Hesabınız kilitlendi. Lütfen yöneticinizle iletişime geçin.',
                ])->withInput($request->only('email', 'remember'));
            }
        }
        
        // If authentication fails
        $attemptsLeft = 5 - RateLimiter::attempts($throttleKey);
        
        $errorMessage = 'Girilen bilgilerle eşleşen bir kayıt bulunamadı. ' . 
                      ($attemptsLeft > 0 ? "Kalan deneme hakkınız: $attemptsLeft" : '');
        
        if ($wantsJson) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 401);
        }
        
        // For web requests, use withErrors and flash to session
        return back()
            ->withErrors(['email' => $errorMessage])
            ->withInput($request->only('email', 'remember'))
            ->with('error', $errorMessage); // Also add to session flash data for blade template
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        try {
            // Check if the user is active
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Hesabınız henüz aktif değil. Lütfen e-postanızı kontrol edin.']);
            }

            // Prepare update data
            $updateData = [
                'login_attempts' => 0, // Reset login attempts on successful login
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ];

            // Update user data
            $user->update($updateData);

            // Log successful login
            Log::info('User authenticated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            // Redirect based on user level
            if ($user->level == User::LEVEL_ADMIN) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->level == User::LEVEL_DRIVER) {
                return redirect()->route('driver.dashboard'); // sürücü kullanıcı
            } elseif ($user->level == 0) {
                return redirect()->route('customer.dashboard'); // müşteri kullanıcı
            } elseif ($user->level == User::LEVEL_AGENCY) {
                return redirect()->route('agencies.network');
            }
            
            // Bilinmeyen seviye → güvenli fallback olarak logout + login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => 'Hesap seviyeniz tanımlı bir panel ile eşleşmiyor. Yöneticinizle iletişime geçin.']);

        } catch (\Exception $e) {
            Log::error('Error during authentication: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Giriş işlemi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.']);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            // Get the current user before logging out for logging purposes
            $user = Auth::user();
            
            // Log the logout attempt
            if ($user) {
                Log::info('User logged out', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
                
                // Update last logout time if needed
                // $user->update(['last_logout_at' => now()]);
            }
            
            // Perform the logout
            Auth::guard('web')->logout();
            
            // Invalidate the session and regenerate the CSRF token
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Clear panel partition cookies so next login starts clean.
            Cookie::queue(Cookie::forget('_session_partition_hint'));
            Cookie::queue(Cookie::forget($this->buildPartitionCookieName('admin')));
            Cookie::queue(Cookie::forget($this->buildPartitionCookieName('agency')));
            
            return redirect()->route('login')
                ->with('status', 'Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!');
                
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);
            
            // Even if there's an error, we should still try to log the user out
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            Cookie::queue(Cookie::forget('_session_partition_hint'));
            Cookie::queue(Cookie::forget($this->buildPartitionCookieName('admin')));
            Cookie::queue(Cookie::forget($this->buildPartitionCookieName('agency')));
            
            return redirect()->route('login')
                ->with('status', 'Çıkış işlemi başarıyla tamamlandı.');
        }
    }

    protected function applySessionPartition(Request $request, ?User $user): void
    {
        // Devre dışı: SessionPartition middleware'i de kapatıldı.
        // Cookie çakışması "page expired" hatalarına yol açıyordu.
        // Tek panel kullanan kullanıcılar için tek bir session cookie yeterli.
    }

    protected function determineSessionPartition(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return match ($user->level) {
            User::LEVEL_ADMIN => 'admin',
            User::LEVEL_AGENCY => 'agency',
            default => null,
        };
    }

    protected function buildPartitionCookieName(string $partition): string
    {
        $configured = config('session.cookie', Str::slug(config('app.name', 'laravel'), '_') . '_session');
        $base = preg_replace('/_(admin|agency)$/', '', (string) $configured) ?: (string) $configured;

        return $base . '_' . $partition;
    }
}