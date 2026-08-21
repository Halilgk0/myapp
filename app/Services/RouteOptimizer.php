<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Şoförün belirli bir günkü biletlerinin ziyaret sırasını
 * Mapbox Optimization API (trafik dahil) ile, başarısızlıkta
 * haversine + nearest-neighbor heuristic ile hesaplar.
 *
 * Bu logic önceden RouteController içindeydi. Tek noktadan kullanmak için
 * (controller + Ticket model auto-reoptimize) buraya taşındı.
 */
class RouteOptimizer
{
    /**
     * Mapbox Optimization API durak limiti (free tier).
     */
    private const MAX_STOPS = 12;

    /**
     * Şoförün $date günündeki biletlerini optimize et.
     * Başlangıç bileti (is_route_start=true) yoksa hiçbir şey yapmaz.
     *
     * @return array{ok: bool, message: string}
     */
    public function optimize(User $driver, string $date): array
    {
        $tickets = Ticket::with('location')
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', $date)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($t) => $t->location && $t->location->latitude && $t->location->longitude);

        if ($tickets->isEmpty()) {
            return ['ok' => false, 'message' => 'Konumu olan bilet bulunamadı.'];
        }

        $startTicket = $tickets->firstWhere('is_route_start', true);
        if (!$startTicket) {
            return ['ok' => false, 'message' => 'Başlangıç noktası seçilmemiş.'];
        }

        $ordered = collect([$startTicket])->merge(
            $tickets->where('id', '!=', $startTicket->id)->values()
        );

        $skipped = 0;
        if ($ordered->count() > self::MAX_STOPS) {
            $skipped = $ordered->count() - self::MAX_STOPS;
            $ordered = $ordered->take(self::MAX_STOPS);
        }

        // Tek durak: sadece başlangıcı sırala
        if ($ordered->count() === 1) {
            DB::table('tickets')->where('id', $startTicket->id)->update([
                'route_order' => 1,
                'route_optimized_at' => now(),
            ]);
            return ['ok' => true, 'message' => 'Tek durak rotası hazır.'];
        }

        $waypointTickets = $ordered->values()->all();
        $token = config('services.mapbox.access_token');

        if ($token) {
            $coords = collect($waypointTickets)
                ->map(fn ($t) => $t->location->longitude . ',' . $t->location->latitude)
                ->implode(';');

            $url = 'https://api.mapbox.com/optimized-trips/v1/mapbox/driving-traffic/' . $coords;

            try {
                $response = Http::timeout(15)->get($url, [
                    'source' => 'first',
                    'destination' => 'any',
                    'roundtrip' => 'false',
                    'overview' => 'full',
                    'geometries' => 'geojson',
                    'access_token' => $token,
                ]);

                $data = $response->json();
                if ($response->successful() && ($data['code'] ?? null) === 'Ok' && !empty($data['waypoints'])) {
                    DB::transaction(function () use ($data, $waypointTickets) {
                        foreach ($data['waypoints'] as $i => $wp) {
                            $orderIdx = $wp['waypoint_index'] ?? null;
                            if ($orderIdx === null) continue;
                            $ticket = $waypointTickets[$i] ?? null;
                            if ($ticket) {
                                DB::table('tickets')->where('id', $ticket->id)->update([
                                    'route_order' => $orderIdx + 1,
                                    'route_optimized_at' => now(),
                                ]);
                            }
                        }
                    });

                    $msg = 'Rota başarıyla hesaplandı (trafik dahil).';
                    if ($skipped > 0) $msg .= " {$skipped} bilet sınır nedeniyle dahil edilmedi.";
                    return ['ok' => true, 'message' => $msg];
                }

                Log::warning('Mapbox Optimization API başarısız', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Mapbox Optimization API hata', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: nearest-neighbor (haversine)
        $this->fallbackNearestNeighbor($waypointTickets, $startTicket);
        $msg = 'Rota basit en-yakın algoritmasıyla hesaplandı (Mapbox erişilemedi).';
        if ($skipped > 0) $msg .= " {$skipped} bilet sınır nedeniyle dahil edilmedi.";
        return ['ok' => true, 'message' => $msg];
    }

    protected function fallbackNearestNeighbor(array $tickets, Ticket $startTicket): void
    {
        $remaining = collect($tickets)->where('id', '!=', $startTicket->id)->values()->all();
        $current = $startTicket;
        $ordered = [$startTicket];

        while (!empty($remaining)) {
            $bestIdx = 0;
            $bestDist = INF;
            foreach ($remaining as $i => $t) {
                $d = $this->haversine(
                    (float) $current->location->latitude, (float) $current->location->longitude,
                    (float) $t->location->latitude, (float) $t->location->longitude
                );
                if ($d < $bestDist) {
                    $bestDist = $d;
                    $bestIdx = $i;
                }
            }
            $next = $remaining[$bestIdx];
            $ordered[] = $next;
            $current = $next;
            array_splice($remaining, $bestIdx, 1);
        }

        DB::transaction(function () use ($ordered) {
            foreach ($ordered as $i => $t) {
                DB::table('tickets')->where('id', $t->id)->update([
                    'route_order' => $i + 1,
                    'route_optimized_at' => now(),
                ]);
            }
        });
    }

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
