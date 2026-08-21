<?php

namespace App\Console\Commands;

use App\Models\TicketRequest;
use App\Models\Ticket;
use App\Models\TicketPassenger;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoApproveStreetAgencyTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:auto-approve-street-agency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sokak acentalarından gelen beklemedeki bilet isteklerini belirlenen saatten sonra otomatik olarak onayla';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sokak acentası bilet istekleri kontrol ediliyor...');

        $currentTime = now()->format('H:i');
        $approvedCount = 0;

        // Get all pending ticket requests from street agencies
        $pendingRequests = TicketRequest::with(['tour', 'requester'])
            ->where('status', TicketRequest::STATUS_PENDING)
            ->get();

        foreach ($pendingRequests as $request) {
            // Check if requester is a street agency
            if (!$request->requester || !$request->requester->isAgency()) {
                continue;
            }

            // Pending request must never create accounting/ticket after tour date has passed.
            if ($request->tour_date && \Carbon\Carbon::parse($request->tour_date)->lt(now()->startOfDay())) {
                $this->warn("Atlandı (tarih geçti): request_id={$request->id}, tour_date={$request->tour_date}");
                continue;
            }

            $tour = $request->tour;
            if (!$tour || !$tour->street_agency_auto_approve_time) {
                continue;
            }

            $autoApproveTime = \Carbon\Carbon::parse($tour->street_agency_auto_approve_time)->format('H:i');

            // If current time is after or equal to the auto approve time
            if ($currentTime >= $autoApproveTime) {
                DB::beginTransaction();
                try {
                    // Create the actual ticket
                    $ticket = Ticket::create([
                        'voucher_no' => $request->voucher_no,
                        'customer_name' => $request->customer_name,
                        'customer_phone' => $request->customer_phone,
                        'customer_email' => $request->customer_email,
                        'customer_nationality' => $request->customer_nationality,
                        'tour_id' => $request->tour_id,
                        'tour_date' => $request->tour_date,
                        'tour_name' => $tour->name,
                        'tour_country' => $tour->country,
                        'tour_region' => $tour->city,
                        // Önce acentanın seçtiği saat (poligon-bazlı), yoksa tur saati legacy fallback
                        'pickup_time' => $request->pickup_time ?: $tour->pickup_time,
                        'sales_agency' => $request->requester->agency 
                            ? $request->requester->agency->name 
                            : $request->requester->name,
                        'total_price' => $request->total_price,
                        'deposit' => $request->total_price * 0.3,
                        'rest' => $request->total_price * 0.7,
                        'currency' => $request->currency,
                        'is_active' => true,
                        'entry_date' => now()->format('Y-m-d'),
                        'entry_time' => now()->format('H:i:s'),
                        'pickup_location' => $request->pickup_location,
                        'room_number' => $request->room_number,
                        'passport_numbers' => $request->passport_numbers,
                        'created_by_user_id' => $request->requester_id,
                    ]);

                    // Create passenger records
                    if ($request->adult_count > 0) {
                        TicketPassenger::create([
                            'ticket_id' => $ticket->id,
                            'passenger_type' => 'adult',
                            'quantity' => $request->adult_count,
                            'unit_price' => $request->adult_price,
                            'total_price' => $request->adult_count * $request->adult_price,
                        ]);
                    }

                    if ($request->child_count > 0) {
                        TicketPassenger::create([
                            'ticket_id' => $ticket->id,
                            'passenger_type' => 'child',
                            'quantity' => $request->child_count,
                            'unit_price' => $request->child_price,
                            'total_price' => $request->child_count * $request->child_price,
                        ]);
                    }

                    if ($request->infant_count > 0) {
                        TicketPassenger::create([
                            'ticket_id' => $ticket->id,
                            'passenger_type' => 'infant',
                            'quantity' => $request->infant_count,
                            'unit_price' => $request->infant_price,
                            'total_price' => $request->infant_count * $request->infant_price,
                        ]);
                    }

                    // Update request status
                    $request->update([
                        'status' => TicketRequest::STATUS_APPROVED,
                        'responded_at' => now(),
                    ]);

                    DB::commit();
                    $approvedCount++;

                    $this->info("✓ Bilet isteği otomatik onaylandı: {$ticket->tracking_no} (Tur: {$tour->name}, Saat: {$autoApproveTime})");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("✗ Bilet isteği onaylanamadı (ID: {$request->id}): {$e->getMessage()}");
                }
            }
        }

        if ($approvedCount > 0) {
            $this->info("Toplam {$approvedCount} bilet isteği otomatik olarak onaylandı.");
        } else {
            $this->info('Otomatik onaylanacak bilet isteği bulunamadı.');
        }

        return Command::SUCCESS;
    }
}
