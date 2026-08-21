<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketPassenger;
use App\Models\TicketRequest;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketRequestController extends Controller
{
    /**
     * Display a listing of pending ticket requests for the current admin.
     */
    public function index()
    {
        $user = Auth::user();

        $pendingRequests = TicketRequest::forOwner($user->id)
            ->pending()
            ->with(['requester.agency', 'tour'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $processedRequests = TicketRequest::forOwner($user->id)
            ->whereIn('status', [TicketRequest::STATUS_APPROVED, TicketRequest::STATUS_REJECTED])
            ->with(['requester.agency', 'tour'])
            ->orderBy('responded_at', 'desc')
            ->limit(20)
            ->get();

        // Geri gönderilen istekler
        $returnedRequests = TicketRequest::forOwner($user->id)
            ->where('status', TicketRequest::STATUS_RETURNED)
            ->with(['requester.agency', 'tour'])
            ->orderBy('returned_at', 'desc')
            ->get();

        return view('admin.ticket-requests.index', compact('pendingRequests', 'processedRequests', 'returnedRequests'));
    }

    /**
     * Show a specific ticket request.
     */
    public function show(TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği görüntüleme yetkiniz yok.');
        }

        $ticketRequest->load(['requester.agency', 'tour']);

        return view('admin.ticket-requests.show', compact('ticketRequest'));
    }

    /**
     * Approve a ticket request and create the actual ticket.
     */
    public function approve(TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği onaylama yetkiniz yok.');
        }

        if (!$ticketRequest->isPending() && !$ticketRequest->isReturned()) {
            return back()->with('error', 'Bu istek zaten işlenmiş.');
        }

        $tour = $ticketRequest->tour;
        $baseCurrency = strtoupper($ticketRequest->base_currency ?: ($ticketRequest->currency ?: 'TRY'));
        $saleCurrency = strtoupper($ticketRequest->sale_currency ?: ($ticketRequest->currency ?: $baseCurrency));
        $restAmount = (float) ($ticketRequest->rest_adjustment_amount ?? 0);
        $restCurrency = strtoupper($ticketRequest->rest_adjustment_currency ?: $saleCurrency);
        $restConverted = (float) ($ticketRequest->rest_converted_amount ?? ($restCurrency === $baseCurrency ? $restAmount : 0));

        $baseTotal = ((int) $ticketRequest->adult_count * (float) $ticketRequest->adult_price)
            + ((int) $ticketRequest->child_count * (float) $ticketRequest->child_price)
            + ((int) $ticketRequest->infant_count * (float) $ticketRequest->infant_price);
        $ownerShareAmount = max($baseTotal - $restConverted, 0);

        // Create the actual ticket
        $ticket = Ticket::create([
            'voucher_no' => $ticketRequest->voucher_no,
            'customer_name' => $ticketRequest->customer_name,
            'customer_phone' => $ticketRequest->customer_phone,
            'customer_email' => $ticketRequest->customer_email,
            'customer_nationality' => $ticketRequest->customer_nationality,
            'tour_id' => $ticketRequest->tour_id,
            'tour_date' => $ticketRequest->tour_date,
            'tour_name' => $tour->name,
            'tour_country' => $tour->country,
            'tour_region' => $tour->city,
            'pickup_time' => $ticketRequest->pickup_time ?: $tour->pickup_time,
            'sales_agency' => $ticketRequest->requester->agency 
                ? $ticketRequest->requester->agency->name 
                : $ticketRequest->requester->name,
            'total_price' => $ticketRequest->total_price,
            'deposit' => $ticketRequest->total_price * 0.3,
            'rest' => $ticketRequest->total_price * 0.7,
            'currency' => $saleCurrency,
            'base_currency' => $baseCurrency,
            'sale_currency' => $saleCurrency,
            'is_active' => true,
            'entry_date' => now()->format('Y-m-d'),
            'entry_time' => now()->format('H:i:s'),
            'pickup_location' => $ticketRequest->pickup_location,
            'room_number' => $ticketRequest->room_number,
            'passport_numbers' => $ticketRequest->passport_numbers,
            'created_by_user_id' => $ticketRequest->requester_id,
            'adult_count' => $ticketRequest->adult_count,
            'child_count' => $ticketRequest->child_count,
            'infant_count' => $ticketRequest->infant_count,
            'adult_price' => $ticketRequest->adult_price,
            'child_price' => $ticketRequest->child_price,
            'infant_price' => $ticketRequest->infant_price,
            'owner_share_amount' => $ownerShareAmount,
            'owner_share_currency' => $baseCurrency,
            'rest_adjustment_amount' => $restAmount,
            'rest_adjustment_currency' => $restCurrency,
            'rest_converted_amount' => $restConverted,
            'rest_fx_rate' => $ticketRequest->rest_fx_rate,
            'rest_fx_source_currency' => $ticketRequest->rest_fx_source_currency ?: $restCurrency,
            'rest_fx_target_currency' => $ticketRequest->rest_fx_target_currency ?: $baseCurrency,
            'rest_fx_date' => $ticketRequest->rest_fx_date,
        ]);

        // Create passenger records
        if ($ticketRequest->adult_count > 0) {
            TicketPassenger::create([
                'ticket_id' => $ticket->id,
                'passenger_type' => 'adult',
                'quantity' => $ticketRequest->adult_count,
                'unit_price' => $ticketRequest->adult_price,
                'total_price' => $ticketRequest->adult_count * $ticketRequest->adult_price,
            ]);
        }

        if ($ticketRequest->child_count > 0) {
            TicketPassenger::create([
                'ticket_id' => $ticket->id,
                'passenger_type' => 'child',
                'quantity' => $ticketRequest->child_count,
                'unit_price' => $ticketRequest->child_price,
                'total_price' => $ticketRequest->child_count * $ticketRequest->child_price,
            ]);
        }

        if ($ticketRequest->infant_count > 0) {
            TicketPassenger::create([
                'ticket_id' => $ticket->id,
                'passenger_type' => 'infant',
                'quantity' => $ticketRequest->infant_count,
                'unit_price' => $ticketRequest->infant_price,
                'total_price' => $ticketRequest->infant_count * $ticketRequest->infant_price,
            ]);
        }

        if ($ticketRequest->pickup_lat && $ticketRequest->pickup_lng) {
            \App\Models\TicketLocation::updateOrCreate(
                ['ticket_id' => $ticket->id],
                ['latitude' => (float)$ticketRequest->pickup_lat, 'longitude' => (float)$ticketRequest->pickup_lng, 'accuracy' => null]
            );
        }

        $ticketRequest->update([
            'status' => TicketRequest::STATUS_APPROVED,
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.ticket-requests.index')
            ->with('success', 'Bilet isteği onaylandı. Bilet oluşturuldu: ' . $ticket->tracking_no);
    }

    /**
     * Reject a ticket request.
     */
    public function reject(Request $request, TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği reddetme yetkiniz yok.');
        }

        if (!$ticketRequest->isPending()) {
            return back()->with('error', 'Bu istek zaten işlenmiş.');
        }

        $ticketRequest->update([
            'status' => TicketRequest::STATUS_REJECTED,
            'rejection_reason' => $request->input('rejection_reason'),
            'responded_at' => now(),
        ]);

        return redirect()->route('admin.ticket-requests.index')
            ->with('success', 'Bilet isteği reddedildi.');
    }

    /**
     * Show the form for editing a ticket request.
     */
    public function edit(TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği düzenleme yetkiniz yok.');
        }

        if (!$ticketRequest->isPending() && !$ticketRequest->isReturned()) {
            return back()->with('error', 'Bu istek zaten işlenmiş, düzenlenemez.');
        }

        $ticketRequest->load(['requester.agency', 'tour']);
        
        // Admin'in sahip olduğu turları getir
        $tours = Tour::where('owner_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.ticket-requests.edit', compact('ticketRequest', 'tours'));
    }

    /**
     * Update the specified ticket request.
     */
    public function update(Request $request, TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği güncelleme yetkiniz yok.');
        }

        if (!$ticketRequest->isPending() && !$ticketRequest->isReturned()) {
            return back()->with('error', 'Bu istek zaten işlenmiş, güncellenemez.');
        }

        $validated = $request->validate([
            'voucher_no' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_nationality' => 'required|string|max:5',
            'tour_date' => 'required|date',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'required|integer|min:0',
            'infant_count' => 'required|integer|min:0',
            'adult_price' => 'required|numeric|min:0',
            'child_price' => 'required|numeric|min:0',
            'infant_price' => 'required|numeric|min:0',
            'pickup_location' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'currency' => 'required|string|max:5',
        ]);

        // Toplam fiyat hesapla
        $totalPrice = ($validated['adult_count'] * $validated['adult_price']) +
                      ($validated['child_count'] * $validated['child_price']) +
                      ($validated['infant_count'] * $validated['infant_price']);

        $validated['total_price'] = $totalPrice;

        // Eğer returned durumundaysa, pending'e çevir
        if ($ticketRequest->isReturned()) {
            $validated['status'] = TicketRequest::STATUS_PENDING;
        }

        $ticketRequest->update($validated);

        return redirect()->route('admin.ticket-requests.show', $ticketRequest)
            ->with('success', 'Bilet isteği başarıyla güncellendi.');
    }

    /**
     * Return a ticket request to agency for editing.
     */
    public function returnToAgency(Request $request, TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->tour_owner_id !== $user->id) {
            abort(403, 'Bu isteği geri gönderme yetkiniz yok.');
        }

        if (!$ticketRequest->isPending()) {
            return back()->with('error', 'Bu istek zaten işlenmiş.');
        }

        $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        $ticketRequest->update([
            'status' => TicketRequest::STATUS_RETURNED,
            'return_reason' => $request->input('return_reason'),
            'returned_at' => now(),
            'return_count' => $ticketRequest->return_count + 1,
        ]);

        return redirect()->route('admin.ticket-requests.index')
            ->with('success', 'Bilet isteği düzenleme için acentaya geri gönderildi.');
    }
}


