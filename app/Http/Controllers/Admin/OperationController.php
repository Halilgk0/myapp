<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ticket;
use App\Models\Tour;
use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OperationController extends Controller
{
    /**
     * Operasyon yönetimi ana sayfası
     */
    public function index()
    {
        $drivers = User::where('level', 2)->with(['vehicle', 'tickets', 'guide'])->get();
        $vehicles = Vehicle::with(['driver.guide', 'tickets'])
            ->whereHas('driver')
            ->get();
        $tickets = Ticket::with(['driver', 'vehicle', 'tour', 'passengers'])->get();

        // Expire past-date tickets: deactivate and clear assignments
        foreach ($tickets as $ticket) {
            $ticket->expireIfPast();
        }
        $tours = Tour::with(['tickets'])->get();
        $guides = Guide::with(['drivers'])->get();

        // Debug bilgisi
        \Log::info('OperationController Debug:', [
            'drivers_count' => $drivers->count(),
            'vehicles_count' => $vehicles->count(),
            'tickets_count' => $tickets->count(),
            'tours_count' => $tours->count(),
            'guides_count' => $guides->count(),
            'drivers' => $drivers->pluck('name', 'id')->toArray(),
            'vehicles' => $vehicles->pluck('plate_number', 'id')->toArray(),
            'tickets' => $tickets->pluck('tracking_no', 'id')->toArray(),
            'guides' => $guides->pluck('name', 'id')->toArray(),
        ]);

        return view('admin.operations.index', compact('drivers', 'vehicles', 'tickets', 'tours', 'guides'));
    }

    /**
     * Şoförü araca ata
     */
    public function assignDriverToVehicle(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        try {
            $driver = User::findOrFail($request->driver_id);
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            if (!$vehicle->driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Şoförsüz araca bilet atanamaz. Lütfen şoför atanmış bir araç seçin.'
                ], 400);
            }

            // Eğer araç zaten başka bir şoföre atanmışsa, o atamayı kaldır
            if ($vehicle->driver_id) {
                $oldDriver = User::find($vehicle->driver_id);
                if ($oldDriver) {
                    $oldDriver->update(['vehicle_id' => null]);
                }
            }

            // Şoförün önceki aracı varsa o aracın driver_id'sini temizle
            if ($driver->vehicle_id) {
                $oldVehicle = Vehicle::find($driver->vehicle_id);
                if ($oldVehicle) {
                    $oldVehicle->update(['driver_id' => null]);
                }
            }

            // Şoförü araca ata ve aracı şoföre ata
            $driver->update(['vehicle_id' => $vehicle->id]);
            $vehicle->update(['driver_id' => $driver->id]);

            return response()->json([
                'success' => true,
                'message' => "Şoför {$driver->name} araca {$vehicle->plate_number} atandı",
                'driver' => $driver->load('vehicle'),
                'vehicle' => $vehicle->load('driver')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Atama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Şoförü araçtan çıkar
     */
    public function removeDriverFromVehicle(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        try {
            $driver = User::findOrFail($request->driver_id);
            $vehicle = $driver->vehicle;

            // Şoförün vehicle_id'sini temizle
            $driver->update(['vehicle_id' => null]);
            
            // Aracın driver_id'sini temizle
            if ($vehicle) {
                $vehicle->update(['driver_id' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => "Şoför {$driver->name} araçtan çıkarıldı",
                'driver' => $driver->load('vehicle'),
                'vehicle' => $vehicle ? $vehicle->load('driver') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çıkarma sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bileti şoföre ata
     */
    public function assignTicketToDriver(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'driver_id' => 'required|exists:users,id'
        ]);

        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            // Block expired tickets
            if ($ticket->tour_date && $ticket->tour_date->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu biletin tarihi geçmiş. Şoföre/araça atanamaz.',
                    'warning_type' => 'ticket_expired'
                ], 400);
            }
            $driver = User::findOrFail($request->driver_id);

            // Check if driver has an assigned vehicle
            if (!$driver->vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Şoförün atanmış aracı yok. Önce şoförü bir araca atayın.'
                ], 400);
            }

            // Nationality check: Check if driver supports ticket's nationality
            if ($ticket->customer_nationality && !$driver->supportsNationality($ticket->customer_nationality)) {
                $nationalityName = \App\Models\Ticket::getNationalityOptions()[$ticket->customer_nationality] ?? $ticket->customer_nationality;
                return response()->json([
                    'success' => false,
                    'message' => "Şoför {$driver->name} bu milliyetten ({$nationalityName}) yolcuları desteklemiyor. Desteklenen milliyetler: {$driver->supported_nationalities_names}"
                ], 400);
            }

            // Vehicle capacity check (per day)
            $vehicle = $driver->vehicle;
            $ticketDate = $ticket->tour_date ? $ticket->tour_date->toDateString() : null;
            $currentPassengers = 0;
            if ($ticketDate) {
                $ticketsSameDay = $vehicle->tickets()
                    ->whereDate('tour_date', $ticketDate)
                    ->with('passengers')
                    ->get();
                $currentPassengers = $ticketsSameDay->sum(function($t) {
                    return $t->passengers->sum('quantity');
                });
            }

            $ticketPassengers = $ticket->passengers->sum('quantity');
            $availableSeats = $vehicle->capacity - $currentPassengers;

            if ($ticketPassengers > $availableSeats) {
                return response()->json([
                    'success' => false,
                    'message' => "Araç kapasitesi yetersiz. Mevcut: {$currentPassengers}/{$vehicle->capacity}, Gerekli: {$ticketPassengers}, Kalan: {$availableSeats}"
                ], 400);
            }

            $ticket->update([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id
            ]);
            return response()->json([
                'success' => true,
                'message' => "Bilet {$ticket->voucher_no} şoför {$driver->name} atandı",
                'ticket' => $ticket->load('driver'),
                'driver' => $driver->load('tickets')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Atama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bileti araca ata
     */
    public function assignTicketToVehicle(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'force_assign' => 'sometimes|boolean'
        ]);

        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            // Block expired tickets
            if ($ticket->tour_date && $ticket->tour_date->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu biletin tarihi geçmiş. Araca atanamaz.',
                    'warning_type' => 'ticket_expired'
                ], 400);
            }
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            // Vehicle capacity check (per day)
            $ticketDate = $ticket->tour_date ? $ticket->tour_date->toDateString() : null;
            $currentPassengers = 0;
            if ($ticketDate) {
                $ticketsSameDay = $vehicle->tickets()
                    ->whereDate('tour_date', $ticketDate)
                    ->with('passengers')
                    ->get();
                $currentPassengers = $ticketsSameDay->sum(function($t) {
                    return $t->passengers->sum('quantity');
                });
            }

            $ticketPassengers = $ticket->passengers->sum('quantity');
            $availableSeats = $vehicle->capacity - $currentPassengers;

            if ($ticketPassengers > $availableSeats) {
                return response()->json([
                    'success' => false,
                    'message' => "Araç kapasitesi yetersiz. Mevcut: {$currentPassengers}/{$vehicle->capacity}, Gerekli: {$ticketPassengers}, Kalan: {$availableSeats}"
                ], 400);
            }

            // Nationality check: Check both driver and guide support (skip if force_assign is true)
            if ($vehicle->driver && $ticket->customer_nationality && !$request->get('force_assign', false)) {
                $driver = $vehicle->driver;
                $nationalityName = \App\Models\Ticket::getNationalityOptions()[$ticket->customer_nationality] ?? $ticket->customer_nationality;
                
                // İlk önce rehber kontrolü
                if ($driver->guide) {
                    if (!$driver->guide->supportsNationality($ticket->customer_nationality)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Rehber {$driver->guide->name} bu milliyetten ({$nationalityName}) yolcuları desteklemiyor. Desteklenen milliyetler: {$driver->guide->supported_nationalities_names}. Devam etmek istiyor musunuz?",
                            'require_confirmation' => true,
                            'warning_type' => 'nationality_not_supported'
                        ], 400);
                    }
                } else {
                    // Eğer rehber yoksa şoförün kendi milliyetlerini kontrol et
                    if (!$driver->supportsNationality($ticket->customer_nationality)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Şoför {$driver->name} bu milliyetten ({$nationalityName}) yolcuları desteklemiyor ve bir rehbere atanmamış. Desteklenen milliyetler: {$driver->supported_nationalities_names}. Devam etmek istiyor musunuz?",
                            'require_confirmation' => true,
                            'warning_type' => 'nationality_not_supported'
                        ], 400);
                    }
                }
            }

            // Assign ticket to vehicle (and to driver if vehicle has one)
            $ticket->update([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $vehicle->driver_id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bilet {$ticket->voucher_no} araca {$vehicle->plate_number} atandı",
                'ticket' => $ticket->load('vehicle'),
                'vehicle' => $vehicle->load('tickets')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Atama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bileti araçtan çıkar
     */
    public function removeTicketFromDriver(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id'
        ]);

        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            $driver = $ticket->driver;

            $ticket->update(['driver_id' => null]);

            return response()->json([
                'success' => true,
                'message' => "Bilet {$ticket->tracking_no} şoförden çıkarıldı",
                'ticket' => $ticket->load('driver'),
                'driver' => $driver ? $driver->load('tickets') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çıkarma sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bileti tura ata
     */
    public function assignTicketToTour(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'tour_id' => 'required|exists:tours,id'
        ]);

        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            $tour = Tour::findOrFail($request->tour_id);

            $ticket->update(['tour_id' => $tour->id]);

            return response()->json([
                'success' => true,
                'message' => "Bilet {$ticket->tracking_no} tur {$tour->name} atandı",
                'ticket' => $ticket->load('tour'),
                'tour' => $tour->load('tickets')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Atama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bileti turdan çıkar
     */
    public function removeTicketFromTour(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id'
        ]);

        try {
            $ticket = Ticket::findOrFail($request->ticket_id);
            $tour = $ticket->tour;

            $ticket->update(['tour_id' => null]);

            return response()->json([
                'success' => true,
                'message' => "Bilet {$ticket->tracking_no} turdan çıkarıldı",
                'ticket' => $ticket->load('tour'),
                'tour' => $tour ? $tour->load('tickets') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Çıkarma sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sistem durumu verilerini getir
     */
    public function getSystemStatus(): JsonResponse
    {
        $stats = [
            'total_drivers' => User::where('level', 2)->count(),
            'assigned_drivers' => User::where('level', 2)->whereNotNull('vehicle_id')->count(),
            'total_vehicles' => Vehicle::count(),
            'assigned_vehicles' => Vehicle::whereNotNull('driver_id')->count(),
            'total_tickets' => Ticket::count(),
            'assigned_tickets' => Ticket::whereNotNull('driver_id')->count(),
            'total_tours' => Tour::count(),
            'tickets_with_tours' => Ticket::whereNotNull('tour_id')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Çoklu bileti araca ata
     */
    public function assignMultipleTicketsToVehicle(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'required|exists:tickets,id',
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        try {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $ticketIds = $request->ticket_ids;
            $tickets = Ticket::whereIn('id', $ticketIds)->get();

            if (!$vehicle->driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Şoförsüz araca bilet atanamaz. Lütfen şoför atanmış bir araç seçin.'
                ], 400);
            }
            
            $successCount = 0;
            $failedTickets = [];
            
            foreach ($tickets as $ticket) {
                try {
                    // Block expired tickets
                    if ($ticket->tour_date && $ticket->tour_date->isPast()) {
                        $failedTickets[] = "#{$ticket->voucher_no} - Tarihi geçmiş (atanamaz)";
                        continue;
                    }
                    // Milliyetleri kontrol et
                    if ($vehicle->driver && $ticket->customer_nationality) {
                        $driver = $vehicle->driver;
                        if (!$driver->supportsNationality($ticket->customer_nationality)) {
                            $nationalityName = \App\Models\Ticket::getNationalityOptions()[$ticket->customer_nationality] ?? $ticket->customer_nationality;
                            $failedTickets[] = "#{$ticket->voucher_no} - Şoför {$driver->name} bu milliyetten ({$nationalityName}) yolcuları desteklemiyor";
                            continue;
                        }
                    }
                    
                    // Bileti ata
                    $ticket->vehicle_id = $vehicle->id;
                    $ticket->driver_id = $vehicle->driver_id; // Aracın şoförünü bileti de ata
                    $ticket->save();
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $failedTickets[] = "#{$ticket->voucher_no} - " . $e->getMessage();
                }
            }
            
            // Sonuç mesajı hazırla
            $message = "{$successCount} bilet başarıyla atandı";
            if (!empty($failedTickets)) {
                $message .= ". Başarısız olanlar: " . implode(', ', $failedTickets);
            }
            
            if ($successCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'assigned_count' => $successCount,
                    'failed_count' => count($failedTickets)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Hiçbir bilet atanamadı. ' . implode(', ', $failedTickets)
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biletler atanırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Şoförü rehbere ata
     */
    public function assignDriverToGuide(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'guide_id' => 'required|exists:guides,id'
        ]);

        try {
            $driver = User::findOrFail($request->driver_id);
            $guide = Guide::findOrFail($request->guide_id);

            // Eski rehberi temizle
            if ($driver->guide_id) {
                $oldGuide = Guide::find($driver->guide_id);
                // Eski rehberin başka ilişkileri varsa onları da temizleyebiliriz
            }

            // Yeni rehberi ata
            $driver->guide_id = $guide->id;
            $driver->save();

            return response()->json([
                'success' => true,
                'message' => $driver->name . ' şoförü ' . $guide->name . ' rehberine atandı.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Şoför rehbere atanırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Şoförü rehberden çıkar
     */
    public function removeDriverFromGuide(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        try {
            $driver = User::findOrFail($request->driver_id);
            
            if (!$driver->guide_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu şoför zaten bir rehbere atanmamış.'
                ], 400);
            }

            $guideName = $driver->guide ? $driver->guide->name : 'Bilinmeyen rehber';
            
            $driver->guide_id = null;
            $driver->save();

            return response()->json([
                'success' => true,
                'message' => $driver->name . ' şoförü ' . $guideName . ' rehberinden çıkarıldı.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Şoför rehberden çıkarılırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
} 