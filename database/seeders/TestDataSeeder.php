<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\Ticket;
use App\Models\TicketPassenger;
use App\Models\DriverActivity;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'level' => 1, // Admin level
                'phone_number' => '5551112233',
                'is_active' => true,
            ]
        );

        // 2. Driver Users
        $driver1 = User::firstOrCreate(
            ['email' => 'driver1@example.com'],
            [
                'name' => 'Test Driver',
                'password' => Hash::make('password'),
                'level' => 2, // Driver level
                'phone_number' => '5554445566',
                'is_active' => true,
                'supported_nationalities' => ['TR', 'DE', 'RU'], // Türk, Alman, Rus yolcular
                'vehicle_id' => null, // Will be set later
            ]
        );

        $driver2 = User::firstOrCreate(
            ['email' => 'driver2@example.com'],
            [
                'name' => 'Ahmet Yılmaz',
                'password' => Hash::make('password'),
                'level' => 2, // Driver level
                'phone_number' => '5554445567',
                'is_active' => true,
                'supported_nationalities' => ['EN', 'FR', 'IT'], // İngilizce, Fransızca, İtalyanca konuşan yolcular
                'vehicle_id' => null, // Will be set later
            ]
        );

        // 3. Vehicles
        $vehicle1 = Vehicle::firstOrCreate(
            ['plate_number' => '34ABC123'],
            [
                'brand' => 'Mercedes',
                'model' => 'Sprinter',
                'vehicle_type' => 'Minibus',
                'color' => 'Beyaz',
                'capacity' => 16,
                'image' => 'vehicles/default.jpg',
                'is_active' => true,
                'driver_id' => $driver1->id, // Assign driver to vehicle
            ]
        );

        $vehicle2 = Vehicle::firstOrCreate(
            ['plate_number' => '06DEF456'],
            [
                'brand' => 'Ford',
                'model' => 'Transit',
                'vehicle_type' => 'Minibus',
                'color' => 'Gri',
                'capacity' => 12,
                'image' => 'vehicles/default.jpg',
                'is_active' => true,
                'driver_id' => null, // No driver assigned
            ]
        );

        // Update drivers to be assigned to vehicles
        $driver1->vehicle_id = $vehicle1->id;
        $driver1->save();

        // Create tours
        $tour1 = Tour::firstOrCreate([
            'name' => 'İstanbul Şehir Turu',
        ], [
            'description' => 'İstanbul\'un tarihi ve kültürel yerlerini kapsayan kapsamlı tur',
            'country' => 'Türkiye',
            'city' => 'İstanbul',
            'pickup_time' => '08:00',
            'dropoff_time' => '18:00',
            'pickup_location' => 'Taksim Meydanı',
            'dropoff_location' => 'Taksim Meydanı',
            'price_adult' => 500.00,
            'price_child' => 250.00,
            'price_infant' => 0.00,
            'currency' => 'TRY',
            'max_capacity' => 20,
            'is_active' => true,
            'notes' => 'Rehber eşliğinde profesyonel tur',
        ]);

        $tour2 = Tour::firstOrCreate([
            'name' => 'Kapadokya Turu',
        ], [
            'description' => 'Kapadokya\'nın eşsiz doğal güzelliklerini keşfedin',
            'country' => 'Türkiye',
            'city' => 'Nevşehir',
            'pickup_time' => '07:00',
            'dropoff_time' => '19:00',
            'pickup_location' => 'Nevşehir Havalimanı',
            'dropoff_location' => 'Nevşehir Havalimanı',
            'price_adult' => 1200.00,
            'price_child' => 600.00,
            'price_infant' => 0.00,
            'currency' => 'TRY',
            'max_capacity' => 15,
            'is_active' => true,
            'notes' => 'Balon turu dahil',
        ]);

        // 5. Tickets - Some assigned to vehicle, some not
        $ticket1 = Ticket::firstOrCreate(
            ['voucher_no' => 'ABC-12345-XYZ'],
            [
                'entry_date' => now()->toDateString(),
                'entry_time' => '08:30',
                'tour_id' => $tour1->id,
                'tour_date' => now()->addDays(5)->toDateString(), // 5 gün sonra İstanbul Turu
                'pickup_time' => $tour1->pickup_time,
                'tour_country' => $tour1->country,
                'tour_region' => $tour1->city,
                'tour_name' => $tour1->name,
                'sales_agency' => 'Test Agency 1',
                'customer_name' => 'Test Customer',
                'customer_phone' => '5557778899',
                'customer_nationality' => 'TR', // Türk müşteri
                'pickup_location' => 'Hotel A',
                'room_number' => '101',
                'passport_numbers' => 'P12345678',
                'total_price' => 2000.00, // 2 adults
                'deposit' => 400.00,
                'rest' => 1600.00,
                'currency' => 'TRY',
                'notes' => 'Customer requested window seat.',
                'driver_id' => $driver1->id,
                'vehicle_id' => $vehicle1->id, // Assigned to vehicle
                'is_active' => true,
            ]
        );

        // Ticket 1 için passenger bilgileri
        TicketPassenger::create([
            'ticket_id' => $ticket1->id,
            'passenger_type' => 'adult',
            'quantity' => 2,
            'unit_price' => $tour1->price_adult,
            'total_price' => 2 * $tour1->price_adult,
        ]);

        $ticket2 = Ticket::firstOrCreate(
            ['voucher_no' => 'ABC-12345-XYZ2'],
            [
                'entry_date' => now()->toDateString(),
                'entry_time' => '13:00',
                'tour_id' => $tour2->id,
                'tour_date' => now()->addDays(10)->toDateString(), // 10 gün sonra Kapadokya Turu
                'pickup_time' => $tour2->pickup_time,
                'tour_country' => $tour2->country,
                'tour_region' => $tour2->city,
                'tour_name' => $tour2->name,
                'sales_agency' => 'Test Agency 2',
                'customer_name' => 'Test Customer 2',
                'customer_phone' => '5559990011',
                'customer_nationality' => 'DE', // Alman müşteri
                'pickup_location' => 'Hotel B',
                'room_number' => '202',
                'passport_numbers' => 'P87654321',
                'total_price' => 3750.00, // 1 adult + 1 child
                'deposit' => 750.00,
                'rest' => 3000.00,
                'currency' => 'TRY',
                'notes' => 'Child seat required.',
                'driver_id' => null, // Not assigned to driver
                'vehicle_id' => null, // Not assigned to vehicle
                'is_active' => true,
            ]
        );

        // Create passengers for ticket2
        TicketPassenger::create([
            'ticket_id' => $ticket2->id,
            'passenger_type' => 'adult',
            'quantity' => 1,
            'unit_price' => $tour2->price_adult,
            'total_price' => $tour2->price_adult * 1,
        ]);
        TicketPassenger::create([
            'ticket_id' => $ticket2->id,
            'passenger_type' => 'child',
            'quantity' => 1,
            'unit_price' => $tour2->price_child,
            'total_price' => $tour2->price_child * 1,
        ]);

        $ticket3 = Ticket::firstOrCreate(
            ['voucher_no' => 'ABC-12345-XYZ3'],
            [
                'entry_date' => now()->toDateString(),
                'entry_time' => '14:00',
                'tour_id' => $tour1->id,
                'tour_date' => now()->toDateString(),
                'pickup_time' => $tour1->pickup_time,
                'tour_country' => $tour1->country,
                'tour_region' => $tour1->city,
                'tour_name' => $tour1->name,
                'sales_agency' => 'Test Agency 3',
                'customer_name' => 'Test Customer 3',
                'customer_phone' => '5559990012',
                'customer_nationality' => 'RU', // Rus müşteri
                'pickup_location' => 'Hotel C',
                'room_number' => '303',
                'passport_numbers' => 'P87654322',
                'total_price' => 1500.00, // 1 adult + 1 child
                'deposit' => 300.00,
                'rest' => 1200.00,
                'currency' => 'TRY',
                'notes' => 'Special request.',
                'driver_id' => null, // Not assigned to driver
                'vehicle_id' => null, // Not assigned to vehicle
                'is_active' => true,
            ]
        );

        // Create passengers for ticket3
        TicketPassenger::create([
            'ticket_id' => $ticket3->id,
            'passenger_type' => 'adult',
            'quantity' => 1,
            'unit_price' => $tour1->price_adult,
            'total_price' => $tour1->price_adult * 1,
        ]);
        TicketPassenger::create([
            'ticket_id' => $ticket3->id,
            'passenger_type' => 'child',
            'quantity' => 1,
            'unit_price' => $tour1->price_child,
            'total_price' => $tour1->price_child * 1,
        ]);

        // 6. Create driver activities for testing
        if ($driver1->activities()->count() == 0) {
            DriverActivity::create([
                'driver_id' => $driver1->id,
                'activity_type' => 'vehicle_assigned',
                'description' => "Araç atandı: {$vehicle1->plate_number}",
                'metadata' => ['vehicle_plate' => $vehicle1->plate_number],
                'recorded_at' => now()->subDays(2),
            ]);

            DriverActivity::create([
                'driver_id' => $driver1->id,
                'activity_type' => 'login',
                'description' => 'Sisteme giriş yapıldı',
                'recorded_at' => now()->subDays(1)->addHours(2),
            ]);

            DriverActivity::create([
                'driver_id' => $driver1->id,
                'activity_type' => 'location_update',
                'description' => 'Konum güncellendi',
                'metadata' => ['latitude' => 41.0082, 'longitude' => 28.9784],
                'recorded_at' => now()->subDays(1)->addHours(3),
            ]);

            DriverActivity::create([
                'driver_id' => $driver1->id,
                'activity_type' => 'trip_start',
                'description' => 'Sefer başladı: İstanbul Şehir Turu',
                'metadata' => ['trip_details' => 'İstanbul Şehir Turu başladı'],
                'recorded_at' => now()->subHours(2),
            ]);
        }

        $this->command->info('Test data created successfully!');
        $this->command->info("Admin: admin@example.com / password");
        $this->command->info("Driver 1: driver1@example.com / password");
        $this->command->info("Driver 2: driver2@example.com / password");
        $this->command->info("Vehicle 1: 34ABC123 (16 capacity, assigned to driver)");
        $this->command->info("Vehicle 2: 06DEF456 (12 capacity, no driver)");
        $this->command->info("Tickets: ABC-12345-XYZ (assigned), ABC-12345-XYZ2 (unassigned), ABC-12345-XYZ3 (unassigned)");
        $this->command->info("Tours: İstanbul Şehir Turu, Kapadokya Turu");
    }
} 