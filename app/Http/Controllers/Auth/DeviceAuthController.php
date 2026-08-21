<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeviceAuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Check if this is an API request
            $wantsJson = $request->wantsJson() || $request->is('api/*');

            // Attempt to authenticate the user
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                
                // Check if user is active
                if (!$user->is_active) {
                    Auth::logout();
                    $message = 'Hesabınız aktif değil. Lütfen yöneticinizle iletişime geçin.';
                    
                    if ($wantsJson) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 403);
                    }
                    
                    return redirect()->back()->with('error', $message);
                }

                // Update last login info
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'login_attempts' => 0
                ]);

                // If this is an API request, return token
                if ($wantsJson) {
                    $token = $user->createToken('device-token')->plainTextToken;
                    return response()->json([
                        'success' => true,
                        'token' => $token,
                        'user' => $user->only(['id', 'name', 'email', 'level']),
                        'redirect' => $user->level == 1 ? route('admin.dashboard') : route('device.dashboard')
                    ]);
                }

                // For web requests, redirect to the appropriate dashboard
                return $user->level == 1 
                    ? redirect()->route('admin.dashboard')
                    : redirect()->route('device.dashboard');
            }

            // If authentication fails
            $message = 'E-posta adresi veya şifre hatalı.';
            
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 401);
            }
            
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $message]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $request->email ?? 'unknown',
                'ip' => $request->ip()
            ]);

            $message = 'Giriş işlemi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
            
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $message]);
        }
    }
    /**
     * Show the device activation form.
     *
     * @param string $token The activation token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showActivationForm($token)
    {
        try {
            // Log the activation attempt
            Log::info('Activation form accessed', [
                'token' => $token,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Find user by activation token
            $user = User::where('activation_token', $token)->first();

            // Check if token is valid
            if (!$user) {
                Log::warning('Invalid activation token provided', [
                    'token' => $token,
                    'ip' => request()->ip()
                ]);
                return redirect()->route('login')
                    ->with('error', 'Geçersiz veya süresi dolmuş aktivasyon bağlantısı.');
            }

            // Check if user is already active
            if ($user->is_active) {
                Log::info('User attempted to activate an already active account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip()
                ]);
                return redirect()->route('login')
                    ->with('status', 'Hesabınız zaten aktif. Giriş yapabilirsiniz.');
            }
            
            // Check if token is expired (older than 24 hours)
            if ($user->activation_token_sent_at && $user->activation_token_sent_at->diffInHours(now()) > 24) {
                Log::warning('Expired activation token used', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token_sent_at' => $user->activation_token_sent_at,
                    'ip' => request()->ip()
                ]);
                
                // Generate a new token
                $newToken = Str::random(60);
                
                // Update user with new token and timestamp
                $user->update([
                    'activation_token' => $newToken,
                    'activation_token_sent_at' => now()
                ]);
                
                // Send the new activation email
                $user->notify(new \App\Notifications\SendActivationNotification($newToken));
                
                Log::info('New activation token generated and sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip()
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Aktivasyon bağlantısının süresi dolmuş. Lütfen yöneticinizden yeni bir bağlantı isteyin.');
            }

            // Show the activation form
            return view('auth.device-activate', [
                'token' => $token,
                'email' => $user->email,
                'name' => $user->name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in showActivationForm: ' . $e->getMessage(), [
                'exception' => $e,
                'token' => $token,
                'ip' => request()->ip()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Aktivasyon işlemi sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        }
    }
    
    /**
     * Activate the device user account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'token' => 'required|string|size:60',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/u',
            ],
        ], [
            'token.required' => 'Geçersiz aktivasyon kodu.',
            'token.size' => 'Geçersiz aktivasyon kodu uzunluğu.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Girilen şifreler eşleşmiyor.',
            'password.regex' => 'Şifre en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.',
        ]);

        try {
            // Find user by activation token
            $user = User::where('activation_token', $request->token)->first();

            // Check if token is valid
            if (!$user) {
                Log::warning('Invalid activation token provided during activation', [
                    'token' => $request->token,
                    'ip' => $request->ip()
                ]);
                return back()->withInput()
                    ->with('error', 'Geçersiz aktivasyon kodu. Lütfen yöneticinizle iletişime geçin.');
            }

            // Check if user is already active
            if ($user->is_active) {
                Log::info('User attempted to activate an already active account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);
                return redirect()->route('login')
                    ->with('status', 'Hesabınız zaten aktif. Giriş yapabilirsiniz.');
            }
            
            // Check if token is expired (older than 24 hours)
            if ($user->activation_token_sent_at && $user->activation_token_sent_at->diffInHours(now()) > 24) {
                Log::warning('Expired activation token used during activation', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token_sent_at' => $user->activation_token_sent_at,
                    'ip' => $request->ip()
                ]);
                
                return back()->withInput()
                    ->with('error', 'Aktivasyon bağlantısının süresi dolmuş. Lütfen yöneticinizden yeni bir bağlantı isteyin.');
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Update user account
                $user->password = Hash::make($validated['password']);
                $user->is_active = true;
                $user->email_verified_at = now();
                $user->activation_token = null;
                $user->activation_token_sent_at = null;
                $user->last_login_at = now();
                $user->last_login_ip = $request->ip();
                $user->save();

                // Commit the transaction
                DB::commit();

                // Log the successful activation
                Log::info('User account activated successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);

                // Log the user in
                Auth::login($user);
                $request->session()->regenerate();
                
                // Redirect to dashboard with success message
                return redirect()->intended(route('device.dashboard'))
                    ->with('success', 'Hesabınız başarıyla aktifleştirildi. Hoş geldiniz!');

            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                throw $e; // Re-throw to be caught by the outer catch
            }

        } catch (\Exception $e) {
            Log::error('Error activating user account: ' . $e->getMessage(), [
                'exception' => $e,
                'token' => $request->token ?? null,
                'ip' => $request->ip()
            ]);
            
            return back()->withInput()
                ->with('error', 'Hesap aktifleştirilirken bir hata oluştu. Lütfen tekrar deneyin. ' . 
                'Sorun devam ederse lütfen yöneticinizle iletişime geçin.');
        }
    }
    
    /**
     * Show the device dashboard
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $user = Auth::user();
        $device = $user->device;
        
        if (!$device) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Bu hesaba bağlı bir cihaz bulunamadı.');
        }
        
        return view('device.dashboard', compact('device'));
    }
    
    /**
     * Update device location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->device) {
            return response()->json([
                'success' => false,
                'message' => 'Bu hesaba bağlı bir cihaz bulunamadı.'
            ], 400);
        }
        
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);
        
        $device = $user->device;
        $device->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'is_online' => true,
            'last_update' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Konum başarıyla güncellendi',
            'device' => $device->fresh()
        ]);
    }
    
    /**
     * Show the request new activation link form
     *
     * @return \Illuminate\View\View
     */
    public function showResendActivationForm()
    {
        return view('auth.resend-activation');
    }
    
    /**
     * Handle activation link resend request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendActivationLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.exists' => 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunamadı.',
        ]);

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->firstOrFail();

            // Check if user is already active
            if ($user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu hesap zaten aktif durumda. Giriş yapabilirsiniz.'
                ], 400);
            }

            // Generate a new activation token
            $token = Str::random(60);
            
            // Update user with new token and timestamp
            $user->update([
                'activation_token' => $token,
                'activation_token_sent_at' => now(),
            ]);

            // Send the activation email
            $user->notify(new \App\Notifications\SendActivationNotification($token));
            
            Log::info('Activation email resent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aktivasyon e-postası başarıyla gönderildi. Lütfen e-posta kutunuzu kontrol edin.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error resending activation email: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Aktivasyon e-postası gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.'
            ], 500);
        }
    }
    
    /**
     * Handle logout request
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
            }
            
            // Perform the logout
            Auth::logout();
            
            // Invalidate the session and regenerate the CSRF token
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('status', 'Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!');
                
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id ?? null,
                'ip' => $request->ip()
            ]);
            
            // Even if there's an error, we should still try to log the user out
            Auth::logout();
            $request->session()->invalidate();
            
            return redirect()->route('login')
                ->with('status', 'Çıkış işlemi başarıyla tamamlandı.');
        }
    }
}
