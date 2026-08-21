<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ticket;
use App\Models\Tour;
use App\Models\TicketPassenger;
use Faker\Factory as Faker;

class ExtendedTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('tr_TR'); // Türkçe locale
        
        echo "🚀 Genişletilmiş test verileri oluşturuluyor...\n";

        $adminId = User::where('level', 1)->orderBy('id')->value('id');
        if (!$adminId) {
            $adminId = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'level' => 1,
                'is_active' => true,
            ])->id;
        }
        
        // Turlar oluştur (10 adet)
        $tours = $this->createTours($faker, $adminId);
        echo "✅ " . count($tours) . " tur oluşturuldu\n";
        
        // Araçlar oluştur (15 adet)
        $vehicles = $this->createVehicles($faker);
        echo "✅ " . count($vehicles) . " araç oluşturuldu\n";
        
        // Şoförler oluştur (20 adet)
        $drivers = $this->createDrivers($faker, $vehicles);
        echo "✅ " . count($drivers) . " şoför oluşturuldu\n";
        
        // Biletler oluştur (50 adet)
        $tickets = $this->createTickets($faker, $tours, $drivers, $vehicles);
        echo "✅ " . count($tickets) . " bilet oluşturuldu\n";
        
        // Yolcular oluştur
        $this->createPassengers($faker, $tickets);
        echo "✅ Yolcu bilgileri eklendi\n";
        
        echo "🎉 Tüm test verileri başarıyla oluşturuldu!\n";
    }
    
    private function createTours($faker, int $ownerId): array
    {
        $tourNames = [
            'İstanbul Şehir Turu',
            'Kapadokya Balloons',
            'Antalya Kemer Turu',
            'Pamukkale Hierapolis',
            'Efes Antik Kenti',
            'Bursa Uludağ',
            'Trabzon Uzungöl',
            'Mardin Tarihi Yapılar',
            'Göreme Açık Hava Müzesi',
            'Bodrum Kale Turu'
        ];
        
        $countries = ['Türkiye', 'İtalya', 'Yunanistan', 'Bulgaristan'];
        $cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Nevşehir', 'Bursa', 'Trabzon', 'Mardin'];
        
        $tours = [];
        foreach ($tourNames as $index => $name) {
            $tour = Tour::create([
                'name' => $name,
                'description' => $faker->paragraph(3),
                'country' => $faker->randomElement($countries),
                'city' => $faker->randomElement($cities),
                'pickup_time' => $faker->time('H:i'),
                'price_adult' => $faker->numberBetween(150, 500),
                'price_child' => $faker->numberBetween(75, 250),
                'price_infant' => 0,
                'currency' => 'TRY',
                'max_capacity' => $faker->numberBetween(10, 50),
                'is_active' => $faker->randomElement([true, false]),
                'owner_id' => $ownerId,
            ]);
            $tours[] = $tour;
        }
        
        return $tours;
    }
    
    private function createVehicles($faker): array
    {
        $brands = ['Mercedes', 'Ford', 'Volkswagen', 'Iveco', 'MAN', 'Scania', 'Volvo'];
        $models = ['Sprinter', 'Transit', 'Crafter', 'Daily', 'TGE', 'Touring', 'Master'];
        $colors = ['Beyaz', 'Gri', 'Mavi', 'Kırmızı', 'Siyah', 'Gümüş'];
        
        $vehicles = [];
        for ($i = 1; $i <= 15; $i++) {
            $vehicle = Vehicle::create([
                'plate_number' => $faker->regexify('[0-9]{2} [A-Z]{1,3} [0-9]{3,4}'),
                'brand' => $faker->randomElement($brands),
                'model' => $faker->randomElement($models),
                'year' => $faker->numberBetween(2015, 2024),
                'color' => $faker->randomElement($colors),
                'capacity' => $faker->randomElement([8, 12, 16, 20, 25, 30, 35]),
                'fuel_type' => $faker->randomElement(['Dizel', 'Benzin', 'LPG', 'Elektrik']),
                'status' => $faker->randomElement(['Aktif', 'Bakımda', 'Servis Dışı']),
                'driver_id' => null, // Sonra atanacak
            ]);
            $vehicles[] = $vehicle;
        }
        
        return $vehicles;
    }
    
    private function createDrivers($faker, $vehicles): array
    {
        $firstNames = ['Ahmet', 'Mehmet', 'Ali', 'Hasan', 'Hüseyin', 'Mustafa', 'Osman', 'İbrahim', 'Yusuf', 'Süleyman',
                      'Fatma', 'Ayşe', 'Emine', 'Hatice', 'Zeynep', 'Elif', 'Meryem', 'Büşra', 'Şeyma', 'Seda'];
        $lastNames = ['Yılmaz', 'Kaya', 'Demir', 'Şahin', 'Çelik', 'Aydın', 'Özkan', 'Arslan', 'Doğan', 'Kılıç',
                     'Aslan', 'Polat', 'Koç', 'Erdoğan', 'Öztürk', 'Güler', 'Korkmaz', 'Tuncer', 'Bayraktar'];
        
        $nationalities = ['TR', 'DE', 'RU', 'EN', 'FR', 'ES', 'IT', 'NL'];
        
        $drivers = [];
        for ($i = 1; $i <= 20; $i++) {
            $firstName = $faker->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);
            
            $driver = User::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@example.com'),
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'license_number' => $faker->regexify('[A-Z][0-9]{8}'),
                'license_expiry' => $faker->dateTimeBetween('now', '+5 years'),
                'level' => 2, // Driver level
                'status' => $faker->randomElement(['Aktif', 'İzinli', 'Servis Dışı']),
                'hire_date' => $faker->dateTimeBetween('-5 years', 'now'),
                'salary' => $faker->numberBetween(8000, 15000),
                'supported_nationalities' => $faker->randomElements($nationalities, $faker->numberBetween(2, 5)),
                'vehicle_id' => null, // Sonra atanacak
            ]);
            
            $drivers[] = $driver;
        }
        
        // Şoförleri araçlara ata (bazılarını boş bırak)
        $availableVehicles = collect($vehicles)->shuffle();
        $assignedDrivers = collect($drivers)->shuffle()->take(12); // 12 şoförü ata, 8'ini boş bırak
        
        foreach ($assignedDrivers as $index => $driver) {
            if (isset($availableVehicles[$index])) {
                $vehicle = $availableVehicles[$index];
                $driver->vehicle_id = $vehicle->id;
                $driver->save();
                
                $vehicle->driver_id = $driver->id;
                $vehicle->save();
            }
        }
        
        return $drivers;
    }
    
    private function createTickets($faker, $tours, $drivers, $vehicles): array
    {
        $firstNames = ['Ahmet', 'Mehmet', 'Ali', 'Fatma', 'Ayşe', 'Zeynep', 'Mustafa', 'Elif', 'Hasan', 'Emine',
                      'John', 'Sarah', 'Michael', 'Emma', 'David', 'Lisa', 'Hans', 'Anna', 'Pierre', 'Marie'];
        $lastNames = ['Johnson', 'Smith', 'Brown', 'Davis', 'Wilson', 'Miller', 'Taylor', 'Anderson', 'Thomas', 'Jackson',
                     'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Schulz',
                     'Yılmaz', 'Kaya', 'Demir', 'Şahin', 'Çelik', 'Aydın', 'Özkan', 'Arslan', 'Doğan'];
        
        $nationalities = ['TR', 'DE', 'RU', 'EN', 'FR', 'ES', 'IT', 'NL', 'US', 'GB'];
        $statuses = ['Yeni', 'Onaylandı', 'İptal', 'Tamamlandı'];
        
        $tickets = [];
        for ($i = 1; $i <= 50; $i++) {
            $tour = $faker->randomElement($tours);
            $firstName = $faker->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);
            $customerNationality = $faker->randomElement($nationalities);
            
            // Tur tarihini hesapla
            $tourDate = $faker->dateTimeBetween('now', '+3 months');
            
            // Bazı biletleri şoför/araca ata
            $assignDriver = $faker->boolean(60); // %60 şans
            $assignVehicle = $faker->boolean(70); // %70 şans
            
            $driverId = null;
            $vehicleId = null;
            
            if ($assignDriver && !empty($drivers)) {
                $driver = $faker->randomElement($drivers);
                $driverId = $driver->id;
                
                if ($assignVehicle && $driver->vehicle_id) {
                    $vehicleId = $driver->vehicle_id;
                }
            } elseif ($assignVehicle && !empty($vehicles)) {
                $vehicle = $faker->randomElement($vehicles);
                $vehicleId = $vehicle->id;
                $driverId = $vehicle->driver_id;
            }
            
            $ticket = Ticket::create([
                'voucher_no' => 'TUR-' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-' . strtoupper($faker->lexify('???')),
                'tracking_no' => strtoupper($faker->bothify('##??##??')),
                'customer_name' => $firstName . ' ' . $lastName,
                'customer_phone' => $faker->phoneNumber,
                'customer_email' => strtolower($firstName . '.' . $lastName . $i . '@email.com'),
                'customer_nationality' => $customerNationality,
                'pickup_location' => $faker->streetAddress,
                'room_number' => $faker->numberBetween(100, 999),
                'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
                'entry_date' => $faker->dateTimeBetween('-30 days', 'now'),
                'entry_time' => $faker->time('H:i:s'),
                'pickup_time' => $tour->pickup_time,
                'total_price' => $faker->numberBetween(100, 1000),
                'status' => $faker->randomElement($statuses),
                'tour_id' => $tour->id,
                'tour_date' => $tourDate,
                'tour_country' => $tour->country,
                'tour_region' => $tour->city,
                'tour_name' => $tour->name,
                'driver_id' => $driverId,
                'vehicle_id' => $vehicleId,
                'sales_agency' => $faker->company,
                'notes' => $faker->optional(0.3)->sentence,
            ]);
            
            $tickets[] = $ticket;
        }
        
        return $tickets;
    }
    
    private function createPassengers($faker, $tickets): void
    {
        $passengerTypes = ['adult', 'child', 'infant'];
        $firstNames = ['Ahmet', 'Mehmet', 'Ali', 'Fatma', 'Ayşe', 'Zeynep', 'John', 'Sarah', 'Hans', 'Anna', 'Pierre', 'Marie'];
        $lastNames = ['Yılmaz', 'Johnson', 'Müller', 'García', 'Rossi', 'Dubois', 'Williams', 'Brown', 'Schmidt'];
        
        foreach ($tickets as $ticket) {
            $passengerCount = $faker->numberBetween(1, 5); // 1-5 yolcu
            
            for ($i = 1; $i <= $passengerCount; $i++) {
                $passengerType = $faker->randomElement($passengerTypes);
                $unitPrice = $passengerType === 'adult' ? $faker->numberBetween(150, 300) : 
                            ($passengerType === 'child' ? $faker->numberBetween(100, 200) : 0);
                
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_name' => $faker->randomElement($firstNames) . ' ' . $faker->randomElement($lastNames),
                    'passenger_type' => $passengerType,
                    'age' => $passengerType === 'adult' ? $faker->numberBetween(18, 65) : 
                            ($passengerType === 'child' ? $faker->numberBetween(3, 17) : $faker->numberBetween(0, 2)),
                    'quantity' => 1,
                    'unit_price' => $unitPrice,
                ]);
            }
        }
    }
}