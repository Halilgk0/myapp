<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketLocation;
use App\Models\TicketPassenger;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2 test şoförü + her birine bir araç + yarın tarihli birden fazla bilet.
 * Yolcu kombinasyonu, fiyat ve para birimi çeşitliliği gerçekçi tutulur.
 *
 *   php artisan db:seed --class=TestRouteSeeder
 */
class TestRouteSeeder extends Seeder
{
    public function run(): void
    {
        $tomorrow = Carbon::now('Europe/Istanbul')->addDay()->toDateString();

        // --- 2 test şoförü (varsa tekrar oluşturma) ---
        $driver1 = User::firstOrCreate(
            ['email' => 'sofor1@test.local'],
            [
                'name'         => 'Test Şoför 1',
                'password'     => Hash::make('admin'),
                'phone_number' => '+905550001111',
                'is_active'    => true,
                'level'        => User::LEVEL_DRIVER,
            ]
        );

        $driver2 = User::firstOrCreate(
            ['email' => 'sofor2@test.local'],
            [
                'name'         => 'Test Şoför 2',
                'password'     => Hash::make('admin'),
                'phone_number' => '+905550002222',
                'is_active'    => true,
                'level'        => User::LEVEL_DRIVER,
            ]
        );

        $this->ensureVehicleFor($driver1, 'TEST-1');
        $this->ensureVehicleFor($driver2, 'TEST-2');

        $tours = Tour::where('is_active', true)->orderBy('id')->limit(2)->get();
        if ($tours->isEmpty()) {
            $this->command->warn('Aktif tur bulunamadı. Önce en az bir tur oluşturun.');
            return;
        }

        // Marmaris bölgesi gerçek karasal koordinatlar (OSM doğrulu)
        $driverPassengers = [
            $driver1->id => [
                [
                    'name' => 'Ayşe Demir', 'phone' => '+905320101111',
                    'lat' => 36.8540, 'lng' => 28.2740,
                    'time' => '08:30', 'pickup' => 'Marmaris Merkez — Atatürk Caddesi',
                    'adult' => 2, 'child' => 0, 'infant' => 0,
                    'currency' => 'EUR', 'adult_price' => 45, 'child_price' => 25, 'infant_price' => 0,
                    'nationality' => 'TR', 'room' => '212',
                ],
                [
                    'name' => 'John Smith Family', 'phone' => '+905320102222',
                    'lat' => 36.8090, 'lng' => 28.2380,
                    'time' => '08:45', 'pickup' => 'İçmeler — Otel Bölgesi',
                    'adult' => 2, 'child' => 2, 'infant' => 0,
                    'currency' => 'EUR', 'adult_price' => 45, 'child_price' => 25, 'infant_price' => 0,
                    'nationality' => 'EN', 'room' => '305',
                ],
                [
                    'name' => 'Müller Familie', 'phone' => '+905320103333',
                    'lat' => 36.7720, 'lng' => 28.2470,
                    'time' => '09:15', 'pickup' => 'Turunç Köyü — Pansiyon Sokağı',
                    'adult' => 2, 'child' => 1, 'infant' => 1,
                    'currency' => 'EUR', 'adult_price' => 45, 'child_price' => 25, 'infant_price' => 0,
                    'nationality' => 'DE', 'room' => '118',
                ],
            ],
            $driver2->id => [
                [
                    'name' => 'Mehmet Yılmaz', 'phone' => '+905320204444',
                    'lat' => 36.8580, 'lng' => 28.2440,
                    'time' => '08:00', 'pickup' => 'Beldibi — Sahil Yolu',
                    'adult' => 4, 'child' => 0, 'infant' => 0,
                    'currency' => 'TRY', 'adult_price' => 1500, 'child_price' => 850, 'infant_price' => 0,
                    'nationality' => 'TR', 'room' => '402',
                ],
                [
                    'name' => 'Petrov Семья', 'phone' => '+905320205555',
                    'lat' => 36.8570, 'lng' => 28.2680,
                    'time' => '08:30', 'pickup' => 'Armutalan Mahallesi',
                    'adult' => 2, 'child' => 1, 'infant' => 0,
                    'currency' => 'USD', 'adult_price' => 50, 'child_price' => 30, 'infant_price' => 0,
                    'nationality' => 'RU', 'room' => '7',
                ],
                [
                    'name' => 'Fatma & Ali Ayhan', 'phone' => '+905320206666',
                    'lat' => 36.8380, 'lng' => 28.1500,
                    'time' => '09:30', 'pickup' => 'Bayır Köyü — Köy Meydanı',
                    'adult' => 2, 'child' => 0, 'infant' => 1,
                    'currency' => 'EUR', 'adult_price' => 45, 'child_price' => 25, 'infant_price' => 0,
                    'nationality' => 'TR', 'room' => null,
                ],
            ],
        ];

        $created = 0;

        DB::transaction(function () use ($driverPassengers, $tours, $tomorrow, &$created) {
            $tourIdx = 0;
            foreach ($driverPassengers as $driverId => $passengers) {
                $tour = $tours[$tourIdx % $tours->count()];
                $tourIdx++;

                foreach ($passengers as $idx => $p) {
                    $totalPrice = ($p['adult'] * $p['adult_price'])
                        + ($p['child'] * $p['child_price'])
                        + ($p['infant'] * $p['infant_price']);
                    $deposit = round($totalPrice * 0.3, 2);
                    $rest = round($totalPrice - $deposit, 2);

                    $ticket = Ticket::create([
                        'customer_name'        => $p['name'],
                        'customer_phone'       => $p['phone'],
                        'customer_nationality' => $p['nationality'],
                        'tour_id'              => $tour->id,
                        'tour_date'            => $tomorrow,
                        'tour_name'            => $tour->name,
                        'tour_country'         => $tour->country ?? 'Türkiye',
                        'tour_region'          => $tour->city ?? 'Marmaris',
                        'sales_agency'         => 'Test Seeder',
                        'pickup_time'          => $p['time'],
                        'pickup_location'      => $p['pickup'],
                        'room_number'          => $p['room'] ?? null,
                        'driver_id'            => $driverId,
                        'is_active'            => true,
                        'adult_count'          => $p['adult'],
                        'child_count'          => $p['child'],
                        'infant_count'         => $p['infant'],
                        'adult_price'          => $p['adult_price'],
                        'child_price'          => $p['child_price'],
                        'infant_price'         => $p['infant_price'],
                        'total_price'          => $totalPrice,
                        'deposit'              => $deposit,
                        'rest'                 => $rest,
                        'currency'             => $p['currency'],
                        'base_currency'        => $p['currency'],
                        'sale_currency'        => $p['currency'],
                        'entry_date'           => $tomorrow,
                        'entry_time'           => '08:00:00',
                        'voucher_no'           => 'V' . strtoupper(substr(uniqid(), -5)) . ($idx + 1),
                    ]);

                    // Yolcu satırları (sayı > 0 olan türler için)
                    if ($p['adult'] > 0) {
                        TicketPassenger::create([
                            'ticket_id'      => $ticket->id,
                            'passenger_type' => 'adult',
                            'quantity'       => $p['adult'],
                            'unit_price'     => $p['adult_price'],
                            'total_price'    => $p['adult'] * $p['adult_price'],
                        ]);
                    }
                    if ($p['child'] > 0) {
                        TicketPassenger::create([
                            'ticket_id'      => $ticket->id,
                            'passenger_type' => 'child',
                            'quantity'       => $p['child'],
                            'unit_price'     => $p['child_price'],
                            'total_price'    => $p['child'] * $p['child_price'],
                        ]);
                    }
                    if ($p['infant'] > 0) {
                        TicketPassenger::create([
                            'ticket_id'      => $ticket->id,
                            'passenger_type' => 'infant',
                            'quantity'       => $p['infant'],
                            'unit_price'     => $p['infant_price'],
                            'total_price'    => $p['infant'] * $p['infant_price'],
                        ]);
                    }

                    TicketLocation::create([
                        'ticket_id' => $ticket->id,
                        'latitude'  => $p['lat'],
                        'longitude' => $p['lng'],
                    ]);

                    $created++;
                }
            }
        });

        $this->command->info('Test verisi hazır:');
        $this->command->info('  • Şoför 1: sofor1@test.local (şifre: admin) — Araç: SEED-TEST-1');
        $this->command->info('  • Şoför 2: sofor2@test.local (şifre: admin) — Araç: SEED-TEST-2');
        $this->command->info('  • Tarih: ' . $tomorrow);
        $this->command->info('  • Bilet: ' . $created . ' adet (her şoföre 3, çeşitli yolcu kombinasyonları)');
        $this->command->info('  • Para birimleri: EUR, USD, TRY');
    }

    /**
     * Şoföre bağlı bir test aracı yoksa yarat ya da sahipsiz bir test aracını bağla.
     */
    protected function ensureVehicleFor(User $driver, string $plateSuffix): void
    {
        $existing = Vehicle::where('driver_id', $driver->id)->first();
        if ($existing) {
            if ($driver->vehicle_id !== $existing->id) {
                $driver->update(['vehicle_id' => $existing->id]);
            }
            return;
        }

        $plate = 'SEED-' . $plateSuffix;
        $vehicle = Vehicle::firstOrCreate(
            ['plate_number' => $plate],
            [
                'brand'        => 'Test',
                'model'        => 'Marka',
                'vehicle_type' => 'Minibus',
                'capacity'     => 14,
                'is_active'    => true,
            ]
        );
        $vehicle->update(['driver_id' => $driver->id]);
        $driver->update(['vehicle_id' => $vehicle->id]);
    }
}
