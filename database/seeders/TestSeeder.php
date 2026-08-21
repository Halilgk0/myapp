<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    /**
     * Seed users + admin tours for testing.
     */
    public function run(): void
    {
        // Sabit admin kullanicisi.
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'level' => User::LEVEL_ADMIN,
            ]
        );

        // Sabit sokak acentasi kullanicisi (admin ile baglantisiz).
        User::updateOrCreate(
            ['email' => 'agency@example.com'],
            [
                'name' => 'Street Agency User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'level' => User::LEVEL_AGENCY,
                'vehicle_id' => null,
                'guide_id' => null,
            ]
        );

        // Diger kullanicilar (sadece users tablosu).
        User::factory()->count(24)->create([
            'is_active' => true,
            'level' => User::LEVEL_AGENCY,
        ]);

        $baseTourData = [
            'owner_id' => $admin->id,
            'country' => 'Turkiye',
            'city' => 'Marmaris',
            'district' => 'Merkez',
            'description' => 'Seeder test turu',
            'currency' => 'TRY',
            'max_capacity' => 20,
            'is_active' => true,
        ];

        $tourConfigs = [
            [
                'name' => 'Test Tur 1 - Otomatik Bilet Kabul',
                'auto_approve_tickets' => true,
                'auto_share_on_connect' => false,
                'price_adult' => 650,
                'price_child' => 420,
                'price_infant' => 80,
            ],
            [
                'name' => 'Test Tur 2 - Ikisi Acik',
                'auto_approve_tickets' => true,
                'auto_share_on_connect' => true,
                'price_adult' => 820,
                'price_child' => 510,
                'price_infant' => 110,
            ],
            [
                'name' => 'Test Tur 3 - Sadece Paylasma',
                'auto_approve_tickets' => false,
                'auto_share_on_connect' => true,
                'price_adult' => 540,
                'price_child' => 340,
                'price_infant' => 60,
            ],
            [
                'name' => 'Test Tur 4 - Ikisi Kapali',
                'auto_approve_tickets' => false,
                'auto_share_on_connect' => false,
                'price_adult' => 930,
                'price_child' => 590,
                'price_infant' => 140,
            ],
        ];

        foreach ($tourConfigs as $config) {
            $availableDates = $this->generateRandomDates();
            $datePrices = $this->buildDatePrices(
                $availableDates,
                $config['price_adult'],
                $config['price_child'],
                $config['price_infant']
            );

            Tour::updateOrCreate(
                ['name' => $config['name'], 'owner_id' => $admin->id],
                array_merge($baseTourData, [
                    'price_adult' => $config['price_adult'],
                    'price_child' => $config['price_child'],
                    'price_infant' => $config['price_infant'],
                    'available_dates' => $availableDates,
                    'date_prices' => $datePrices,
                    'auto_approve_tickets' => $config['auto_approve_tickets'],
                    'auto_share_on_connect' => $config['auto_share_on_connect'],
                ])
            );
        }

        // Sabit şoför (users + araç; admin panelindeki oluşturma ile uyumlu seviye).
        $driverUser = User::updateOrCreate(
            ['email' => 'driver@example.com'],
            [
                'name' => 'Test Şoför',
                'password' => Hash::make('password'),
                'phone_number' => '905551990011',
                'email_verified_at' => now(),
                'is_active' => true,
                'level' => User::LEVEL_DRIVER,
            ]
        );

        $seedVehicle = Vehicle::updateOrCreate(
            ['plate_number' => 'TEST-SOFOR-01'],
            [
                'brand' => 'Mercedes',
                'model' => 'Sprinter',
                'vehicle_type' => 'Minibüs',
                'color' => 'Beyaz',
                'capacity' => 16,
                'is_active' => true,
                'driver_id' => $driverUser->id,
            ]
        );

        // Şoför panelinde "bugün" listesi için örnek bilet (aynı gün tur tarihi).
        $demoTour = Tour::where('name', 'Test Tur 2 - Ikisi Acik')->where('owner_id', $admin->id)->first();
        if ($demoTour && $seedVehicle) {
            $today = now()->startOfDay();
            $ticket = Ticket::updateOrCreate(
                ['tracking_no' => 'DRV-SEED-TODAY'],
                [
                    'entry_date' => $today,
                    'entry_time' => now()->format('H:i:s'),
                    'tour_date' => $today,
                    'pickup_time' => '10:30',
                    'tour_country' => $demoTour->country ?? 'Turkiye',
                    'tour_region' => $demoTour->city ?? 'Marmaris',
                    'tour_name' => $demoTour->name,
                    'tour_id' => $demoTour->id,
                    'sales_agency' => 'Seeder Acenta',
                    'customer_name' => 'Örnek Yolcu Grubu',
                    'customer_phone' => '905559998877',
                    'pickup_location' => 'Marmaris İskele',
                    'room_number' => '204',
                    'passport_numbers' => "Ahmet Yılmaz\nAyşe Yılmaz",
                    'total_price' => 1000,
                    'deposit' => 400,
                    'rest' => 600,
                    'currency' => 'TRY',
                    'vehicle_id' => $seedVehicle->id,
                    'driver_id' => $driverUser->id,
                    'is_active' => true,
                    'adult_count' => 2,
                    'child_count' => 1,
                    'infant_count' => 0,
                    'adult_price' => 400,
                    'child_price' => 200,
                    'infant_price' => 0,
                ]
            );

            $ticket->passengers()->delete();
            $ticket->passengers()->createMany([
                [
                    'passenger_type' => 'adult',
                    'quantity' => 2,
                    'unit_price' => 400,
                    'total_price' => 800,
                ],
                [
                    'passenger_type' => 'child',
                    'quantity' => 1,
                    'unit_price' => 200,
                    'total_price' => 200,
                ],
            ]);
        }
    }

    /**
     * Rastgele secili tarihler uretir (bugunden sonraki 90 gun icinden).
     */
    private function generateRandomDates(int $minCount = 10, int $maxCount = 16, int $maxDaysAhead = 90): array
    {
        $targetCount = random_int($minCount, $maxCount);
        $dateSet = [];
        $attempts = 0;

        while (count($dateSet) < $targetCount && $attempts < 500) {
            $offset = random_int(0, $maxDaysAhead);
            $dateKey = now()->copy()->addDays($offset)->format('Y-m-d');
            $dateSet[$dateKey] = true;
            $attempts++;
        }

        $dates = array_keys($dateSet);
        sort($dates);

        return $dates;
    }

    /**
     * Secili tarihler icin rastgele gunluk fiyatlar uretir.
     */
    private function buildDatePrices(array $dates, int $adultBase, int $childBase, int $infantBase): array
    {
        $prices = [];

        foreach ($dates as $date) {
            $adult = max(0, $adultBase + random_int(-90, 160));
            $child = max(0, $childBase + random_int(-70, 120));
            $infant = max(0, $infantBase + random_int(-20, 40));

            $prices[$date] = [
                'adult' => $adult,
                'child' => $child,
                'infant' => $infant,
                'currency' => 'TRY',
            ];
        }

        return $prices;
    }
}


