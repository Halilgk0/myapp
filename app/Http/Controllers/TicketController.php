<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Show the ticket login form.
     */
    public function showLoginForm()
    {
        return view('tickets.login');
    }

    /**
     * Handle ticket login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'tracking_no' => 'required|string|max:20',
        ]);

        $ticket = Ticket::where('tracking_no', $request->tracking_no)
            ->where('is_active', true)
            ->with(['vehicle', 'passengers'])
            ->first();

        if (!$ticket) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Geçersiz bilet numarası veya bilet aktif değil.');
        }

        // Store ticket in session
        session(['ticket_id' => $ticket->id]);

        return redirect()->route('tickets.dashboard')
            ->with('success', 'Bilet bilgileriniz başarıyla yüklendi.');
    }

    /**
     * Show the ticket dashboard.
     */
    public function dashboard()
    {
        $ticketId = session('ticket_id');
        
        if (!$ticketId) {
            return redirect()->route('tickets.login')
                ->with('error', 'Lütfen önce bilet numaranızı girin.');
        }

        $ticket = Ticket::where('id', $ticketId)
            ->where('is_active', true)
            ->with(['vehicle', 'passengers'])
            ->first();

        if (!$ticket) {
            session()->forget('ticket_id');
            return redirect()->route('tickets.login')
                ->with('error', 'Bilet bulunamadı veya aktif değil.');
        }

        return view('tickets.dashboard', compact('ticket'));
    }

    /**
     * Logout from ticket.
     */
    public function logout()
    {
        session()->forget('ticket_id');
        
        return redirect()->route('tickets.login')
            ->with('success', 'Başarıyla çıkış yapıldı.');
    }
}
