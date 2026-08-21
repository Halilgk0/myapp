<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Customer\AuthController;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ticketId = $request->session()->get(AuthController::SESSION_KEY);

        if (!$ticketId) {
            return redirect()->route('customer.login');
        }

        $ticket = Ticket::query()
            ->with(['vehicle.driver', 'tour', 'driver'])
            ->where('id', $ticketId)
            ->where('is_active', true)
            ->first();

        if (!$ticket) {
            $request->session()->forget(AuthController::SESSION_KEY);
            return redirect()->route('customer.login')
                ->withErrors(['voucher_no' => 'Bilete erişim kaybedildi. Tekrar giriş yapın.']);
        }

        $request->attributes->set('customer_ticket', $ticket);

        return $next($request);
    }
}
