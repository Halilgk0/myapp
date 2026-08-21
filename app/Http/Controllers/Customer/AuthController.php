<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public const SESSION_KEY = 'customer_ticket_id';

    public function showLogin()
    {
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'voucher_no' => 'required|string|max:100',
        ], [
            'voucher_no.required' => 'Voucher numaranızı giriniz.',
        ]);

        $key = 'customer-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['voucher_no' => "Çok fazla deneme yapıldı. {$seconds} saniye sonra tekrar deneyin."])->withInput();
        }

        $voucher = trim($data['voucher_no']);

        $ticket = Ticket::query()
            ->where('voucher_no', $voucher)
            ->where('is_active', true)
            ->first();

        if (!$ticket) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['voucher_no' => 'Voucher numarası bulunamadı veya bilet pasif durumda.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->put(self::SESSION_KEY, $ticket->id);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();
        return redirect()->route('customer.login');
    }
}
