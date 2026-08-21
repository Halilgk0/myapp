<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketLocationController extends Controller
{
    /**
     * Update customer location for a specific ticket
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'tracking_no' => 'required|string|exists:tickets,tracking_no',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
            $ticket = Ticket::where('tracking_no', $validated['tracking_no'])->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bilet bulunamadı'
                ], 404);
            }

            // Create or update location record
            TicketLocation::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'accuracy' => $validated['accuracy'] ?? null,
                    'updated_at' => now(),
                ]
            );

            Log::info('Customer location updated', [
                'ticket_id' => $ticket->id,
                'tracking_no' => $ticket->tracking_no,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Konum başarıyla güncellendi'
            ]);

        } catch (\Exception $e) {
            Log::error('Customer location update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Konum güncellenirken bir hata oluştu'
            ], 500);
        }
    }

    /**
     * Get customer locations for admin map
     */
    public function getCustomerLocations()
    {
        try {
            $locations = TicketLocation::with('ticket')
                ->where('updated_at', '>=', now()->subMinutes(30)) // Only recent locations
                ->get()
                ->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'accuracy' => $location->accuracy,
                        'updated_at' => $location->updated_at,
                        'ticket' => [
                            'tracking_no' => $location->ticket->tracking_no,
                            'customer_name' => $location->ticket->customer_name,
                            'tour_name' => $location->ticket->tour_name,
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'customers' => $locations
            ]);

        } catch (\Exception $e) {
            Log::error('Get customer locations error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Konum bilgileri alınamadı'
            ], 500);
        }
    }
} 