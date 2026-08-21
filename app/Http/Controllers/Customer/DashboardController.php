<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\CustomerTranslator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var Ticket $ticket */
        $ticket = $request->attributes->get('customer_ticket');
        $ticket->loadMissing(['vehicle.driver', 'tour', 'driver', 'passengers']);

        $driver = $ticket->driver ?: ($ticket->vehicle?->driver);
        $vehicle = $ticket->vehicle;
        $mapboxToken = config('services.mapbox.access_token');

        $t = CustomerTranslator::strings($ticket->customer_nationality);
        $locale = CustomerTranslator::langFor($ticket->customer_nationality);

        // Tarih formatı için Carbon locale
        try {
            \Carbon\Carbon::setLocale($locale);
        } catch (\Throwable $e) {
            \Carbon\Carbon::setLocale('en');
        }

        return view('customer.dashboard', compact('ticket', 'driver', 'vehicle', 'mapboxToken', 't', 'locale'));
    }

    /**
     * Canlı: şoför konumu + ETA (varış süresi tahmini)
     * Mapbox Directions API ile pickup noktasına gidiş süresi hesaplanır.
     */
    public function liveStatus(Request $request)
    {
        /** @var Ticket $ticket */
        $ticket = $request->attributes->get('customer_ticket');

        $vehicle = $ticket->vehicle ?: $ticket->driver?->vehicle;
        $driver = $ticket->driver ?: $vehicle?->driver;

        $response = [
            'driver_name'      => $driver?->name,
            'driver_phone'     => $driver?->phone_number,
            'vehicle_plate'    => $vehicle?->plate_number,
            'driver_location'  => null,
            'pickup_location'  => null,
            'eta_seconds'      => null,
            'eta_text'         => null,
            'distance_meters'  => null,
            'last_update'      => null,
            'is_live'          => false,
            'has_route_geometry' => false,
            'route_geometry'   => null,
        ];

        // Müşteri pickup noktası
        if ($ticket->relationLoaded('location') === false) {
            $ticket->loadMissing('location');
        }
        if ($ticket->location && $ticket->location->latitude && $ticket->location->longitude) {
            $response['pickup_location'] = [
                'lat' => (float) $ticket->location->latitude,
                'lng' => (float) $ticket->location->longitude,
            ];
        }

        // Son şoför konumu
        if ($vehicle) {
            $loc = $vehicle->locations()->latest('recorded_at')->first();
            if ($loc) {
                $isLive = $loc->recorded_at && $loc->recorded_at->gt(now()->subMinutes(3));
                $response['driver_location'] = [
                    'lat'   => (float) $loc->latitude,
                    'lng'   => (float) $loc->longitude,
                    'speed' => $loc->speed,
                ];
                $response['last_update'] = $loc->recorded_at?->toIso8601String();
                $response['is_live'] = (bool) $isLive;

                // ETA — Mapbox Directions API (driving-traffic)
                $token = config('services.mapbox.access_token');
                if ($token && $response['pickup_location']) {
                    [$from, $to] = [
                        $loc->longitude . ',' . $loc->latitude,
                        $response['pickup_location']['lng'] . ',' . $response['pickup_location']['lat'],
                    ];
                    try {
                        $url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$from};{$to}"
                             . '?geometries=geojson&overview=full&access_token=' . urlencode($token);
                        $ch = curl_init($url);
                        curl_setopt_array($ch, [
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT        => 4,
                            CURLOPT_CONNECTTIMEOUT => 2,
                        ]);
                        $raw = curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        if ($raw && $code === 200) {
                            $data = json_decode($raw, true);
                            $route = $data['routes'][0] ?? null;
                            if ($route) {
                                $seconds = (int) round($route['duration']);
                                $response['eta_seconds']     = $seconds;
                                $response['eta_text']        = CustomerTranslator::formatEta($seconds, $ticket->customer_nationality);
                                $response['distance_meters'] = (int) round($route['distance']);
                                $response['has_route_geometry'] = true;
                                $response['route_geometry']  = $route['geometry'];
                            }
                        }
                    } catch (\Throwable $e) {
                        // ETA başarısız olabilir, sorun değil
                    }
                }
            }
        }

        return response()->json($response);
    }

}
