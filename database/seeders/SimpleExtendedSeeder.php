<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\TicketPassenger;
use App\Models\Guide;
use App\Models\Tour;
use App\Models\Agency;
use App\Models\AgencyConnectionRequest;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SimpleExtendedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('tr_TR');
        
        echo "🚀 Basit genişletilmiş test verileri oluşturuluyor...\n";
        
        // Mevcut tabloları kullanarak daha fazla veri oluştur
        
        // 0. Admin ve sokak acentası kullanıcıları oluştur
        $admin = $this->createAdminUser();
        $agency = $this->createAgencyUser();
        $agencyProfile = $this->createAgencyProfile($agency);
        
        // Admin ve sokak acentası arasında bağlantı oluştur (accepted status)
        $this->createAgencyConnection($admin->id, $agency->id);
        
        echo "✅ Admin ve Sokak Acentası oluşturuldu\n";
        echo "🔗 Admin ve Sokak Acentası birbirine bağlandı\n";
        echo "📧 Admin Email: " . $admin->email . " / password\n";
        echo "📧 Acenta Email: " . $agency->email . " / password\n";
        
        // 1. Özel kurgulu turlar + ilave turlar oluştur
        [$tours, $specialTours] = $this->createTours($faker, 8, $admin->id, $agency->id);
        echo "✅ " . count($tours) . " tur oluşturuldu\n";
        
        // 2. Rehberler oluştur (5 adet)
        $guides = $this->createGuides($faker, 5);
        echo "✅ " . count($guides) . " rehber oluşturuldu\n";
        
        // 3. Daha fazla araç (10 adet)
        $vehicles = $this->createVehicles($faker, 10);
        echo "✅ " . count($vehicles) . " araç oluşturuldu\n";
        
        // 4. Daha fazla şoför (15 adet)
        $drivers = $this->createDrivers($faker, 15);
        echo "✅ " . count($drivers) . " şoför oluşturuldu\n";
        echo "📧 Test Şoför Bilgileri:\n";
        echo "   ahmet.yilmaz1@example.com / password\n";
        echo "   mehmet.kaya2@example.com / password\n";
        echo "   ali.demir3@example.com / password\n";
        echo "   fatma.sahin4@example.com / password\n";
        echo "   ayse.celik5@example.com / password\n";
        
        // 5. Şoförleri araçlara ata
        $this->assignDriversToVehicles($drivers, $vehicles);
        echo "✅ Şoförler araçlara atandı\n";
        
        // 6. Şoförleri rehberlere ata
        $this->assignDriversToGuides($drivers, $guides);
        echo "✅ Şoförler rehberlere atandı\n";
        
        // 7. Daha fazla bilet (30 adet)
        $tickets = $this->createTickets($faker, 30, $drivers, $vehicles, $tours);
        echo "✅ " . count($tickets) . " bilet oluşturuldu\n";

        // 7.b Sokak acentası için özel biletler (geçmiş ve gelecek, bazen üstüne eklenen fiyatla)
        $agencyTickets = $this->createAgencyTickets($faker, $agency, $specialTours);
        $restTickets = $this->createRestTickets($faker, $admin, $agency, $specialTours, $tours);
        $tickets = array_merge($tickets, $agencyTickets, $restTickets);
        echo "✅ Sokak acentası için " . count($agencyTickets) . " özel bilet oluşturuldu\n";
        echo "✅ Rest gelirli süre geçmiş biletler eklendi: " . count($restTickets) . "\n";
        
        // 5. Yolcu bilgileri
        $this->createPassengers($faker, $tickets);
        echo "✅ Yolcu bilgileri eklendi\n";

        // 6. Çeşitli para birimlerinde örnek muhasebe kayıtları ekle
        $this->createSampleTransactions($faker);
        echo "✅ Örnek gelir/gider kayıtları eklendi (çoklu para birimi)\n";

        // 7. Süresi geçmiş biletleri muhasebeye işle
        try {
            Artisan::call('tickets:account-expired');
            echo "✅ Süresi geçmiş bilet gelirleri işlendi: " . Artisan::output() . "\n";
        } catch (\Throwable $e) {
            echo "⚠️ tickets:account-expired çalıştırılamadı: {$e->getMessage()}\n";
        }

        // 8. Maaş ödemelerini oluştur (bugünün günü için ayarlanmış şoförler var)
        try {
            Artisan::call('drivers:pay-salaries');
            echo "✅ Maaş giderleri oluşturuldu: " . Artisan::output() . "\n";
        } catch (\Throwable $e) {
            echo "⚠️ drivers:pay-salaries çalıştırılamadı: {$e->getMessage()}\n";
        }
        
        echo "🎉 Basit genişletilmiş test verileri başarıyla oluşturuldu!\n";
        echo "💡 Giriş yapmak için: http://localhost:8000/login\n";
    }
    
    private function applyOptionalUserColumns(array $data): array
    {
        $optional = [
            'phone_number',
            'supported_nationalities',
            'vehicle_id',
            'guide_id',
            'salary_amount',
            'salary_currency',
            'salary_day',
            'last_salary_paid_at',
            'activation_token',
            'activation_token_sent_at',
            'login_attempts',
            'last_login_at',
        ];

        foreach ($optional as $col) {
            if (!Schema::hasColumn('users', $col)) {
                unset($data[$col]);
            }
        }

        return $data;
    }

    private function createAdminUser(): User
    {
        $data = [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'phone_number' => '+90 555 123 45 67',
            'level' => 1, // Admin level
            'is_active' => true,
            'supported_nationalities' => null,
            'vehicle_id' => null,
            'guide_id' => null,
        ];

        return User::create($this->applyOptionalUserColumns($data));
    }

    private function createAgencyUser(): User
    {
        $data = [
            'name' => 'Sokak Acentası',
            'email' => 'agency@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'phone_number' => '+90 555 987 65 43',
            'level' => 3, // Agency level
            'is_active' => true,
            'supported_nationalities' => null,
            'vehicle_id' => null,
            'guide_id' => null,
        ];

        return User::create($this->applyOptionalUserColumns($data));
    }

    private function createAgencyProfile(User $agency): Agency
    {
        return Agency::create([
            'user_id' => $agency->id,
            'name' => $agency->name,
            'email' => $agency->email,
            'phone' => $agency->phone_number ?? '+90 555 000 00 00',
            'address' => 'İstanbul',
            'contact_person' => $agency->name,
            'website' => null,
            'commission_rate' => 5,
            'notes' => 'Seeder otomatik acenta kaydı',
            'is_active' => true,
        ]);
    }

    /**
     * Admin ve sokak acentası arasında kabul edilmiş bağlantı oluştur
     */
    private function createAgencyConnection(int $adminId, int $agencyId): AgencyConnectionRequest
    {
        return AgencyConnectionRequest::create([
            'requester_id' => $adminId,
            'target_id' => $agencyId,
            'status' => AgencyConnectionRequest::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }
    
    private function createTours($faker, $count, int $ownerId, int $agencyId): array
    {
        $tourNames = [
            'İstanbul Şehir Turu',
            'Kapadokya Balon Turu',
            'Antalya Kemer Turu',
            'Pamukkale Hierapolis',
            'Efes Antik Kenti',
            'Bursa Uludağ Turu',
            'Trabzon Uzungöl',
            'Mardin Tarihi Yapılar'
        ];
        
        $countries = ['Türkiye', 'İtalya', 'Yunanistan', 'Bulgaristan'];
        $cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Nevşehir', 'Bursa', 'Trabzon', 'Mardin'];
        
        $tours = [];

        // 3 özel tur: (1) paylaşılan, (2) sadece auto-approve, (3) paylaşılan + auto-approve
        $baseTime = now('Europe/Istanbul')->setTime(10, 0);

        // Gelecek 60 gün için available_dates ve date_prices oluştur
        $availableDates = [];
        $datePrices = [];
        $datePricesShared = [];
        $datePricesAuto = [];
        $datePricesSharedAuto = [];
        
        for ($d = 0; $d < 60; $d++) {
            $date = now()->addDays($d)->format('Y-m-d');
            // Her 3 günden 2'si açık olsun
            if ($d % 3 !== 2) {
                $availableDates[] = $date;
                
                // Shared tour için fiyatlar (USD)
                $datePricesShared[$date] = [
                    'adult' => 20,
                    'child' => 10,
                    'infant' => 0,
                    'currency' => 'USD',
                ];
                
                // Auto approve tour için fiyatlar (USD)
                $datePricesAuto[$date] = [
                    'adult' => 25,
                    'child' => 12,
                    'infant' => 0,
                    'currency' => 'USD',
                ];
                
                // Shared + Auto tour için fiyatlar (USD)
                $datePricesSharedAuto[$date] = [
                    'adult' => 30,
                    'child' => 15,
                    'infant' => 0,
                    'currency' => 'USD',
                ];
            }
        }
        
        // Geçmiş 60 gün için de ekle (test biletleri için)
        for ($d = 1; $d <= 60; $d++) {
            $date = now()->subDays($d)->format('Y-m-d');
            $availableDates[] = $date;
            
            $datePricesShared[$date] = [
                'adult' => 20,
                'child' => 10,
                'infant' => 0,
                'currency' => 'USD',
            ];
            
            $datePricesAuto[$date] = [
                'adult' => 25,
                'child' => 12,
                'infant' => 0,
                'currency' => 'USD',
            ];
            
            $datePricesSharedAuto[$date] = [
                'adult' => 30,
                'child' => 15,
                'infant' => 0,
                'currency' => 'USD',
            ];
        }

        $sharedTour = Tour::create([
            'name' => 'Paylaşılan Örnek Tur',
            'description' => 'Admin tarafından paylaşılan örnek tur.',
            'country' => 'Türkiye',
            'city' => 'İstanbul',
            'pickup_time' => $baseTime->format('H:i'),
            'price_adult' => 0, // Artık sadece date_prices kullanılıyor
            'price_child' => 0,
            'price_infant' => 0,
            'currency' => 'USD',
            'max_capacity' => 40,
            'is_active' => true,
            'owner_id' => $ownerId,
            'auto_approve_tickets' => false,
            'auto_share_on_connect' => false,
            'street_agency_auto_approve_enabled' => false,
            'available_dates' => $availableDates,
            'date_prices' => $datePricesShared,
        ]);

        $autoApproveTour = Tour::create([
            'name' => 'Oto Onay Tur',
            'description' => 'Otomatik bilet onaylayan tur.',
            'country' => 'Türkiye',
            'city' => 'Antalya',
            'pickup_time' => $baseTime->copy()->addMinutes(30)->format('H:i'),
            'price_adult' => 0,
            'price_child' => 0,
            'price_infant' => 0,
            'currency' => 'USD',
            'max_capacity' => 35,
            'is_active' => true,
            'owner_id' => $ownerId,
            'auto_approve_tickets' => true,
            'auto_share_on_connect' => false,
            'street_agency_auto_approve_enabled' => true,
            'available_dates' => $availableDates,
            'date_prices' => $datePricesAuto,
        ]);

        $sharedAndAuto = Tour::create([
            'name' => 'Paylaşılan + Oto Onay Tur',
            'description' => 'Hem paylaşılan hem otomatik onaylayan tur.',
            'country' => 'Türkiye',
            'city' => 'Kapadokya',
            'pickup_time' => $baseTime->copy()->addHours(1)->format('H:i'),
            'price_adult' => 0,
            'price_child' => 0,
            'price_infant' => 0,
            'currency' => 'USD',
            'max_capacity' => 50,
            'is_active' => true,
            'owner_id' => $ownerId,
            'auto_approve_tickets' => true,
            'auto_share_on_connect' => false,
            'street_agency_auto_approve_enabled' => true,
            'available_dates' => $availableDates,
            'date_prices' => $datePricesSharedAuto,
        ]);

        // Paylaşım pivotu: sharedTour ve sharedAndAuto turları acenta ile paylaştır
        // custom_date_prices ile acenta için özel fiyatlar
        DB::table('tour_shared_users')->insert([
            [
                'tour_id' => $sharedTour->id,
                'shared_by_user_id' => $ownerId,
                'shared_with_user_id' => $agencyId,
                'custom_date_prices' => json_encode($datePricesShared),
                'custom_currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $sharedAndAuto->id,
                'shared_by_user_id' => $ownerId,
                'shared_with_user_id' => $agencyId,
                'custom_date_prices' => json_encode($datePricesSharedAuto),
                'custom_currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $tours[] = $sharedTour;
        $tours[] = $autoApproveTour;
        $tours[] = $sharedAndAuto;

        // Kalan turları rastgele oluştur
        for ($i = 0; $i < $count && $i < count($tourNames); $i++) {
            // Takvimden fiyat girilmemiş turlarda fiyatlar boş kalmalı
            $tourDatePrices = [];
            $tourAvailableDates = [];
            $adultPrice = $faker->numberBetween(150, 500);
            $childPrice = $faker->numberBetween(75, 250);

            $tour = Tour::create([
                'name' => $tourNames[$i],
                'description' => $faker->paragraph(2),
                'country' => $faker->randomElement($countries),
                'city' => $faker->randomElement($cities),
                'pickup_time' => $faker->time('H:i'),
                'price_adult' => 0, // Artık sadece date_prices kullanılıyor
                'price_child' => 0,
                'price_infant' => 0,
                'currency' => 'TRY',
                'max_capacity' => $faker->numberBetween(10, 50),
                'is_active' => true,
                'owner_id' => $ownerId,
                'auto_approve_tickets' => $faker->boolean(40),
                'auto_share_on_connect' => false,
                'street_agency_auto_approve_enabled' => $faker->boolean(50),
                'available_dates' => $tourAvailableDates,
                'date_prices' => $tourDatePrices,
            ]);
            $tours[] = $tour;
        }
        
        return [$tours, [
            'shared' => $sharedTour,
            'auto' => $autoApproveTour,
            'shared_auto' => $sharedAndAuto,
        ]];
    }
    
    private function createVehicles($faker, $count): array
    {
        $brands = ['Mercedes', 'Ford', 'Volkswagen', 'Iveco', 'MAN'];
        $models = ['Sprinter', 'Transit', 'Crafter', 'Daily', 'TGE'];
        $colors = ['Beyaz', 'Gri', 'Mavi', 'Siyah'];
        
        $vehicles = [];
        for ($i = 1; $i <= $count; $i++) {
            $vehicle = Vehicle::create([
                'plate_number' => $faker->regexify('[0-9]{2} [A-Z]{2} [0-9]{3,4}'),
                'brand' => $faker->randomElement($brands),
                'model' => $faker->randomElement($models),
                'vehicle_type' => 'Minibus',
                'color' => $faker->randomElement($colors),
                'capacity' => $faker->randomElement([8, 12, 16, 20, 25, 30]),
                'is_active' => $faker->randomElement([true, false]),
                'driver_id' => null,
            ]);
            $vehicles[] = $vehicle;
        }
        
        return $vehicles;
    }
    
    private function createDrivers($faker, $count): array
    {
        $nationalities = ['TR', 'DE', 'RU', 'EN', 'FR', 'ES', 'IT'];
        $salaryCurrencies = ['TRY','USD','EUR','GBP','RUB'];
        $todayDay = now('Europe/Istanbul')->day;
        
        $drivers = [];
        
        // İlk olarak test için sabit şoförler oluştur
        $fixedDrivers = [
            ['name' => 'Ahmet Yılmaz', 'email' => 'ahmet.yilmaz1@example.com'],
            ['name' => 'Mehmet Kaya', 'email' => 'mehmet.kaya2@example.com'],
            ['name' => 'Ali Demir', 'email' => 'ali.demir3@example.com'],
            ['name' => 'Fatma Şahin', 'email' => 'fatma.sahin4@example.com'],
            ['name' => 'Ayşe Çelik', 'email' => 'ayse.celik5@example.com'],
        ];
        
        foreach ($fixedDrivers as $fixedDriver) {
            $data = [
                'name' => $fixedDriver['name'],
                'email' => $fixedDriver['email'],
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'phone_number' => $faker->phoneNumber,
                'level' => 2, // Driver level
                'is_active' => true, // Sabit şoförleri aktif yap
                'supported_nationalities' => $faker->randomElements($nationalities, $faker->numberBetween(2, 4)),
                'vehicle_id' => null,
                'guide_id' => null,
                'salary_amount' => $faker->numberBetween(12000, 20000),
                'salary_currency' => $faker->randomElement($salaryCurrencies),
                'salary_day' => $todayDay, // İlk sabitler bugün maaş alsın
            ];
            
            $driver = User::create($this->applyOptionalUserColumns($data));
            
            $drivers[] = $driver;
        }
        
        // Geri kalan şoförleri rastgele oluştur
        $firstNames = ['Hasan', 'Mustafa', 'Osman', 'İbrahim', 'Yusuf', 'Süleyman', 'Emine', 'Hatice', 'Zeynep'];
        $lastNames = ['Aydın', 'Özkan', 'Arslan', 'Doğan', 'Kılıç'];
        
        for ($i = 6; $i <= $count; $i++) {
            $firstName = $faker->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);
            
            $driver = User::create($this->applyOptionalUserColumns([
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@example.com'),
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'phone_number' => $faker->phoneNumber,
                'level' => 2, // Driver level
                'is_active' => $faker->randomElement([true, false]),
                'supported_nationalities' => $faker->randomElements($nationalities, $faker->numberBetween(2, 4)),
                'vehicle_id' => null,
                'guide_id' => null,
                'salary_amount' => $faker->numberBetween(9000, 18000),
                'salary_currency' => $faker->randomElement($salaryCurrencies),
                'salary_day' => $faker->randomElement([$todayDay, $faker->numberBetween(1, 10)]),
            ]));
            
            $drivers[] = $driver;
        }
        
        return $drivers;
    }
    
    private function assignDriversToVehicles($drivers, $vehicles): void
    {
        $shuffledDrivers = collect($drivers)->shuffle();
        $shuffledVehicles = collect($vehicles)->shuffle();
        
        // İlk 8 şoförü araçlara ata, geri kalanını boş bırak
        $driversToAssign = $shuffledDrivers->take(8);
        
        foreach ($driversToAssign as $index => $driver) {
            if (isset($shuffledVehicles[$index])) {
                $vehicle = $shuffledVehicles[$index];
                
                $driver->vehicle_id = $vehicle->id;
                $driver->save();
                
                $vehicle->driver_id = $driver->id;
                $vehicle->save();
            }
        }
    }
    
    private function createTickets($faker, $count, $drivers, $vehicles, $tours): array
    {
        $firstNames = ['Ahmet', 'Mehmet', 'Ali', 'Fatma', 'Ayşe', 'John', 'Sarah', 'Hans', 'Anna', 'Pierre'];
        $lastNames = ['Johnson', 'Smith', 'Brown', 'Müller', 'Schmidt', 'Yılmaz', 'Kaya', 'García', 'Rossi'];
        $nationalities = ['TR', 'DE', 'RU', 'EN', 'FR', 'ES', 'IT', 'NL', 'US'];
        $currencies = ['TRY','USD','EUR','GBP','RUB'];
        
        $tickets = [];
        for ($i = 1; $i <= $count; $i++) {
            $firstName = $faker->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);
            $customerNationality = $faker->randomElement($nationalities);
            
            // Rastgele bir tur seç
            $tour = $faker->randomElement($tours);
            
            // Bazı biletleri şoför/araca ata (%60 şans)
            $assignDriver = $faker->boolean(60);
            $assignVehicle = $faker->boolean(70);
            
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
            
            // Tur tarihi: bazen geçmiş, bazen gelecek
            $tourDate = $faker->dateTimeBetween('-40 days', '+60 days');
            $currency = $faker->randomElement($currencies);
            
            // Yolcu sayıları ve fiyatları
            $adultCount = $faker->numberBetween(1, 3);
            $childCount = $faker->numberBetween(0, 2);
            $infantCount = $faker->numberBetween(0, 1);
            $adultPrice = $faker->numberBetween(150, 300);
            $childPrice = $faker->numberBetween(75, 150);
            $infantPrice = 0;
            $totalPrice = ($adultCount * $adultPrice) + ($childCount * $childPrice);
            
            $ticket = Ticket::create([
                'voucher_no' => 'EXT-' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-' . strtoupper($faker->lexify('???')),
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
                'pickup_time' => $faker->time('H:i'),
                'total_price' => $totalPrice,
                'deposit' => round($totalPrice * 0.3),
                'rest' => round($totalPrice * 0.7),
                'currency' => $currency,
                'is_active' => $tourDate >= now(), // geçmiş ise pasif
                'tour_id' => $tour->id,
                'tour_date' => $tourDate->format('Y-m-d'),
                'tour_country' => $tour->country,
                'tour_region' => $tour->city,
                'tour_name' => $tour->name,
                'driver_id' => $driverId,
                'vehicle_id' => $vehicleId,
                'sales_agency' => $faker->company,
                'notes' => $faker->optional(0.3)->sentence,
                'created_by_user_id' => null,
                'adult_count' => $adultCount,
                'child_count' => $childCount,
                'infant_count' => $infantCount,
                'adult_price' => $adultPrice,
                'child_price' => $childPrice,
                'infant_price' => $infantPrice,
            ]);
            
            $tickets[] = $ticket;
        }

        // Ek olarak, garantili süresi geçmiş 5 bilet (farklı para birimleri)
        for ($i = 1; $i <= 5; $i++) {
            $tour = $faker->randomElement($tours);
            $currency = $faker->randomElement($currencies);
            $tourDate = $faker->dateTimeBetween('-60 days', '-10 days');
            
            // Yolcu sayıları ve fiyatları
            $adultCount = $faker->numberBetween(1, 3);
            $childCount = $faker->numberBetween(0, 2);
            $infantCount = $faker->numberBetween(0, 1);
            $adultPrice = $faker->numberBetween(150, 300);
            $childPrice = $faker->numberBetween(75, 150);
            $infantPrice = 0;
            $totalPrice = ($adultCount * $adultPrice) + ($childCount * $childPrice);
            
            $ticket = Ticket::create([
                'voucher_no' => 'EXP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tracking_no' => 'EXP' . strtoupper($faker->bothify('##??')),
                'customer_name' => $faker->name,
                'customer_phone' => $faker->phoneNumber,
                'customer_email' => $faker->safeEmail,
                'customer_nationality' => $faker->randomElement($nationalities),
                'pickup_location' => $faker->streetAddress,
                'room_number' => $faker->numberBetween(100, 999),
                'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
                'entry_date' => $faker->dateTimeBetween('-70 days', '-50 days'),
                'entry_time' => $faker->time('H:i:s'),
                'pickup_time' => $faker->time('H:i'),
                'total_price' => $totalPrice,
                'deposit' => round($totalPrice * 0.3),
                'rest' => round($totalPrice * 0.7),
                'currency' => $currency,
                'is_active' => false,
                'tour_id' => $tour->id,
                'tour_date' => $tourDate->format('Y-m-d'),
                'tour_country' => $tour->country,
                'tour_region' => $tour->city,
                'tour_name' => $tour->name,
                'driver_id' => optional($faker->randomElement($drivers ?? []))->id,
                'vehicle_id' => null,
                'sales_agency' => $faker->company,
                'notes' => 'Süresi geçmiş test bileti',
                'created_by_user_id' => null,
                'adult_count' => $adultCount,
                'child_count' => $childCount,
                'infant_count' => $infantCount,
                'adult_price' => $adultPrice,
                'child_price' => $childPrice,
                'infant_price' => $infantPrice,
            ]);
            $tickets[] = $ticket;
        }
        
        return $tickets;
    }

    /**
     * Sokak acentası için garantili biletler (geçmiş + farklı fiyatlama)
     * - Bazıları tur sahibi taban fiyatıyla
     * - Bazıları üstüne eklenmiş (markup) fiyatla
     */
    private function createAgencyTickets($faker, User $agency, array $specialTours): array
    {
        $tickets = [];
        $agencyName = $agency->name;

        $configs = [
            // geçmiş, taban fiyattan
            ['tourKey' => 'shared', 'daysOffset' => -15, 'markup' => 0],
            // geçmiş, markup
            ['tourKey' => 'shared_auto', 'daysOffset' => -25, 'markup' => 10],
            // geçmiş, markup küçük
            ['tourKey' => 'auto', 'daysOffset' => -5, 'markup' => 5],
            // geçmiş, taban altı (test için -20)
            ['tourKey' => 'shared', 'daysOffset' => -20, 'markup' => -20, 'label' => 'taban altı test'],
            // gelecek, markup
            ['tourKey' => 'shared', 'daysOffset' => 7, 'markup' => 8],
            // gelecek, taban
            ['tourKey' => 'shared_auto', 'daysOffset' => 12, 'markup' => 0],
        ];

        foreach ($configs as $index => $conf) {
            $tour = $specialTours[$conf['tourKey']];
            $tourDate = now()->addDays($conf['daysOffset']);
            $adultCount = 2;
            $childCount = 0;
            $infantCount = 0;

            $basePrices = $tour->getPricesForDate($tourDate);
            $baseTotal = ($adultCount * ($basePrices['adult'] ?? 0))
                + ($childCount * ($basePrices['child'] ?? 0))
                + ($infantCount * ($basePrices['infant'] ?? 0));

            $totalPrice = $baseTotal + $conf['markup'];
            if ($totalPrice < 0) {
                $totalPrice = 0;
            }
            $deposit = $totalPrice * 0.3;
            $rest = $totalPrice - $deposit;

            $ticket = Ticket::create([
                'voucher_no' => 'AG-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'tracking_no' => 'AGC-' . strtoupper($faker->bothify('##??')),
                'customer_name' => $faker->name,
                'customer_phone' => $faker->phoneNumber,
                'customer_email' => $faker->safeEmail,
                'customer_nationality' => 'TR',
                'pickup_location' => $faker->streetAddress,
                'room_number' => $faker->numberBetween(100, 999),
                'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
                'entry_date' => now()->addDays($conf['daysOffset'] - 1),
                'entry_time' => now()->format('H:i:s'),
                'pickup_time' => $tour->pickup_time,
                'total_price' => $totalPrice,
                'deposit' => $deposit,
                'rest' => $rest,
                'currency' => $tour->currency,
                'is_active' => $tourDate->isFuture(),
                'tour_id' => $tour->id,
                'tour_date' => $tourDate->format('Y-m-d'),
                'tour_country' => $tour->country,
                'tour_region' => $tour->city,
                'tour_name' => $tour->name,
                'driver_id' => null,
                'vehicle_id' => null,
                'sales_agency' => $agencyName,
                'notes' => $conf['markup'] < 0
                    ? 'Acenta taban fiyatın altında satış (test)'
                    : ($conf['markup'] > 0 ? 'Acenta ek fiyatlı satış' : 'Acenta taban fiyat'),
                'created_by_user_id' => $agency->id,
                'adult_count' => $adultCount,
                'child_count' => $childCount,
                'infant_count' => $infantCount,
                'adult_price' => $basePrices['adult'] ?? 0,
                'child_price' => $basePrices['child'] ?? 0,
                'infant_price' => $basePrices['infant'] ?? 0,
            ]);

            // Yolcu kaydı (2 yetişkin)
            TicketPassenger::create([
                'ticket_id' => $ticket->id,
                'passenger_type' => 'adult',
                'quantity' => $adultCount,
                'unit_price' => $conf['markup'] > 0 ? ($basePrices['adult'] ?? 0) + ($conf['markup'] / max(1, $adultCount)) : ($basePrices['adult'] ?? 0),
                'total_price' => $totalPrice,
            ]);

            $tickets[] = $ticket;
        }

        // Ek: Geçmiş, taban ve rest farklı para birimi (taban USD, rest RUB)
        $tour = $specialTours['shared'];
        $tourDate = now()->subDays(18);
        $adultCount = 2;
        $childCount = 0;
        $infantCount = 0;
        $basePrices = $tour->getPricesForDate($tourDate);
        $baseTotal = ($adultCount * ($basePrices['adult'] ?? 0))
            + ($childCount * ($basePrices['child'] ?? 0))
            + ($infantCount * ($basePrices['infant'] ?? 0));
        // senaryo: taban 50 USD, rest 100 RUB
        $baseCurrency = 'USD';
        $restCurrency = 'RUB';
        $saleCurrency = 'USD';
        $baseTotal = 50;
        $restAdjust = 100;
        // RUB→USD dönüşümü için varsayılan kur (yaklaşık)
        $restFxRateCross = 0.011;
        $restConvertedCross = round($restAdjust * $restFxRateCross, 2); // 100 RUB ≈ 1.1 USD
        $ownerShareCross = max($baseTotal - $restConvertedCross, 0); // 50 - 1.1 = 48.9 USD
        $saleTotal = 55; // küçük markup
        $deposit = $saleTotal * 0.3;
        $restSalePortion = $saleTotal - $deposit;

        $crossTicket = Ticket::create([
            'voucher_no' => 'AG-FX-0001',
            'tracking_no' => 'AGFX-' . strtoupper($faker->bothify('##??')),
            'customer_name' => $faker->name,
            'customer_phone' => $faker->phoneNumber,
            'customer_email' => $faker->safeEmail,
            'customer_nationality' => 'TR',
            'pickup_location' => $faker->streetAddress,
            'room_number' => $faker->numberBetween(100, 999),
            'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
            'entry_date' => now()->subDays(20),
            'entry_time' => now()->format('H:i:s'),
            'pickup_time' => $tour->pickup_time,
            'total_price' => $saleTotal,
            'deposit' => $deposit,
            'rest' => $restSalePortion,
            'currency' => $saleCurrency,
            'base_currency' => $baseCurrency,
            'sale_currency' => $saleCurrency,
            'owner_share_amount' => $ownerShareCross,
            'owner_share_currency' => $baseCurrency,
            'rest_adjustment_amount' => $restAdjust,
            'rest_adjustment_currency' => $restCurrency,
            'rest_converted_amount' => $restConvertedCross,
            'rest_fx_rate' => $restFxRateCross,
            'rest_fx_source_currency' => $restCurrency,
            'rest_fx_target_currency' => $baseCurrency,
            'rest_fx_date' => now()->subDays(20)->toDateString(),
            'is_active' => false,
            'tour_id' => $tour->id,
            'tour_date' => $tourDate->format('Y-m-d'),
            'tour_country' => $tour->country,
            'tour_region' => $tour->city,
            'tour_name' => $tour->name,
            'driver_id' => null,
            'vehicle_id' => null,
            'sales_agency' => $agencyName,
            'notes' => 'Taban USD, rest RUB (kur uygulanmadı) - süre geçmiş test',
            'created_by_user_id' => $agency->id,
            'adult_count' => $adultCount,
            'child_count' => $childCount,
            'infant_count' => $infantCount,
            'adult_price' => $basePrices['adult'] ?? 0,
            'child_price' => $basePrices['child'] ?? 0,
            'infant_price' => $basePrices['infant'] ?? 0,
        ]);

        TicketPassenger::create([
            'ticket_id' => $crossTicket->id,
            'passenger_type' => 'adult',
            'quantity' => $adultCount,
            'unit_price' => ($basePrices['adult'] ?? 0),
            'total_price' => $saleTotal,
        ]);

        $tickets[] = $crossTicket;

        return $tickets;
    }

    /**
     * Rest gelirli, süresi geçmiş örnek biletler (admin ve sokak acentası için)
     */
    private function createRestTickets($faker, User $admin, User $agency, array $specialTours, array $tours): array
    {
        $tickets = [];

        // 1) Sokak acentası: taban USD, rest EUR, satış USD (süresi geçmiş)
        $tour = $specialTours['shared'];
        $tourDate = now()->subDays(28);
        $adultCount = 2;
        $baseCurrency = 'USD';
        $saleCurrency = 'USD';
        $restCurrency = 'EUR';
        $baseTotal = 60; // taban fiyat
        $restAmount = 35; // EUR
        // EUR→USD dönüşümü için varsayılan kur (yaklaşık)
        $restFxRate = 1.08;
        $restConverted = round($restAmount * $restFxRate, 2); // 35 EUR ≈ 37.8 USD
        $ownerShareAmount = max($baseTotal - $restConverted, 0); // 60 - 37.8 = 22.2 USD
        $saleTotal = 75;  // USD
        $deposit = $saleTotal * 0.3;
        $restSalePortion = $saleTotal - $deposit;

        $agencyRest = Ticket::create([
            'voucher_no' => 'AG-REST-0001',
            'tracking_no' => 'AGRS-' . strtoupper($faker->bothify('##??')),
            'customer_name' => $faker->name,
            'customer_phone' => $faker->phoneNumber,
            'customer_email' => $faker->safeEmail,
            'customer_nationality' => 'TR',
            'pickup_location' => $faker->streetAddress,
            'room_number' => $faker->numberBetween(100, 999),
            'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
            'entry_date' => now()->subDays(30),
            'entry_time' => now()->format('H:i:s'),
            'pickup_time' => $tour->pickup_time,
            'total_price' => $saleTotal,
            'deposit' => $deposit,
            'rest' => $restSalePortion,
            'currency' => $saleCurrency,
            'base_currency' => $baseCurrency,
            'sale_currency' => $saleCurrency,
            'owner_share_amount' => $ownerShareAmount,
            'owner_share_currency' => $baseCurrency,
            'rest_adjustment_amount' => $restAmount,
            'rest_adjustment_currency' => $restCurrency,
            'rest_converted_amount' => $restConverted,
            'rest_fx_rate' => $restFxRate,
            'rest_fx_source_currency' => $restCurrency,
            'rest_fx_target_currency' => $baseCurrency,
            'rest_fx_date' => now()->subDays(30)->toDateString(),
            'is_active' => false,
            'tour_id' => $tour->id,
            'tour_date' => $tourDate->format('Y-m-d'),
            'tour_country' => $tour->country,
            'tour_region' => $tour->city,
            'tour_name' => $tour->name,
            'driver_id' => null,
            'vehicle_id' => null,
            'sales_agency' => $agency->name,
            'notes' => 'Rest EUR, taban USD (geçmiş bilet)',
            'created_by_user_id' => $agency->id,
            'adult_count' => $adultCount,
            'child_count' => 0,
            'infant_count' => 0,
            'adult_price' => $baseTotal / max(1, $adultCount),
            'child_price' => 0,
            'infant_price' => 0,
        ]);

        TicketPassenger::create([
            'ticket_id' => $agencyRest->id,
            'passenger_type' => 'adult',
            'quantity' => $adultCount,
            'unit_price' => $agencyRest->adult_price,
            'total_price' => $saleTotal,
        ]);

        $tickets[] = $agencyRest;

        // 2) Admin kendi satışı: taban/satış EUR, rest USD (süresi geçmiş)
        $tourAdmin = $tours[0] ?? $tour;
        $tourDateAdmin = now()->subDays(35);
        $baseCurrencyA = 'EUR';
        $saleCurrencyA = 'EUR';
        $restCurrencyA = 'USD';
        $baseTotalA = 90;
        $restAmountA = 25;
        // USD→EUR dönüşümü için varsayılan kur (yaklaşık)
        $restFxRateA = 0.93;
        $restConvertedA = round($restAmountA * $restFxRateA, 2); // 25 USD ≈ 23.25 EUR
        $ownerShareAmountA = max($baseTotalA - $restConvertedA, 0); // 90 - 23.25 = 66.75 EUR
        $saleTotalA = 110;
        $depositA = $saleTotalA * 0.3;
        $restSalePortionA = $saleTotalA - $depositA;

        $adminRest = Ticket::create([
            'voucher_no' => 'AD-REST-0001',
            'tracking_no' => 'ADRS-' . strtoupper($faker->bothify('##??')),
            'customer_name' => $faker->name,
            'customer_phone' => $faker->phoneNumber,
            'customer_email' => $faker->safeEmail,
            'customer_nationality' => 'DE',
            'pickup_location' => $faker->streetAddress,
            'room_number' => $faker->numberBetween(200, 899),
            'passport_numbers' => $faker->regexify('[A-Z][0-9]{8}'),
            'entry_date' => now()->subDays(37),
            'entry_time' => now()->format('H:i:s'),
            'pickup_time' => $tourAdmin->pickup_time,
            'total_price' => $saleTotalA,
            'deposit' => $depositA,
            'rest' => $restSalePortionA,
            'currency' => $saleCurrencyA,
            'base_currency' => $baseCurrencyA,
            'sale_currency' => $saleCurrencyA,
            'owner_share_amount' => $ownerShareAmountA,
            'owner_share_currency' => $baseCurrencyA,
            'rest_adjustment_amount' => $restAmountA,
            'rest_adjustment_currency' => $restCurrencyA,
            'rest_converted_amount' => $restConvertedA,
            'rest_fx_rate' => $restFxRateA,
            'rest_fx_source_currency' => $restCurrencyA,
            'rest_fx_target_currency' => $baseCurrencyA,
            'rest_fx_date' => now()->subDays(37)->toDateString(),
            'is_active' => false,
            'tour_id' => $tourAdmin->id,
            'tour_date' => $tourDateAdmin->format('Y-m-d'),
            'tour_country' => $tourAdmin->country,
            'tour_region' => $tourAdmin->city,
            'tour_name' => $tourAdmin->name,
            'driver_id' => null,
            'vehicle_id' => null,
            'sales_agency' => 'Admin',
            'notes' => 'Admin satışı, rest USD (geçmiş bilet)',
            'created_by_user_id' => $admin->id,
            'adult_count' => 2,
            'child_count' => 0,
            'infant_count' => 0,
            'adult_price' => $baseTotalA / 2,
            'child_price' => 0,
            'infant_price' => 0,
        ]);

        TicketPassenger::create([
            'ticket_id' => $adminRest->id,
            'passenger_type' => 'adult',
            'quantity' => 2,
            'unit_price' => $adminRest->adult_price,
            'total_price' => $saleTotalA,
        ]);

        $tickets[] = $adminRest;

        return $tickets;
    }
    
    private function createPassengers($faker, $tickets): void
    {
        foreach ($tickets as $ticket) {
            // Ticket'ta kayıtlı sayıları kullan, yoksa rastgele oluştur
            $adultCount = $ticket->adult_count ?? $faker->numberBetween(1, 3);
            $childCount = $ticket->child_count ?? $faker->numberBetween(0, 2);
            $infantCount = $ticket->infant_count ?? $faker->numberBetween(0, 1);
            
            // Ticket'ta kayıtlı birim fiyatları kullan, yoksa rastgele oluştur
            $adultPrice = $ticket->adult_price ?? $faker->numberBetween(150, 300);
            $childPrice = $ticket->child_price ?? $faker->numberBetween(75, 150);
            $infantPrice = $ticket->infant_price ?? 0;
            
            // Zaten passenger kaydı varsa atla (createAgencyTickets ve createRestTickets kendi passengerlarını oluşturuyor)
            if (TicketPassenger::where('ticket_id', $ticket->id)->exists()) {
                continue;
            }
            
            // Her tip için tek bir kayıt oluştur (quantity ile gruplanmış)
            if ($adultCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'adult',
                    'quantity' => $adultCount,
                    'unit_price' => $adultPrice,
                    'total_price' => $adultCount * $adultPrice,
                ]);
            }
            
            if ($childCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'child',
                    'quantity' => $childCount,
                    'unit_price' => $childPrice,
                    'total_price' => $childCount * $childPrice,
                ]);
            }
            
            if ($infantCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'infant',
                    'quantity' => $infantCount,
                    'unit_price' => $infantPrice,
                    'total_price' => $infantCount * $infantPrice,
                ]);
            }
        }
    }

    private function createSampleTransactions($faker): void
    {
        $currencies = ['TRY','USD','EUR','GBP','RUB'];
        $titlesIncome = ['Online Satış', 'Ofis Satış', 'Tur Komisyonu', 'Ek Hizmet', 'Ekstra Bagaj'];
        $titlesExpense = ['Yakıt', 'Araç Bakım', 'Ofis Kirası', 'Reklam', 'Ekipman'];

        foreach ($currencies as $cur) {
            // 3 gelir
            for ($i=0; $i<3; $i++) {
                Transaction::create([
                    'type' => 'income',
                    'title' => $faker->randomElement($titlesIncome) . " ({$cur})",
                    'amount' => $faker->randomFloat(2, 100, 2000),
                    'currency' => $cur,
                    'transaction_date' => $faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
                    'payment_method' => 'seed',
                    'status' => 'paid',
                    'notes' => 'Seeder gelir kaydı',
                    'created_by' => null,
                ]);
            }
            // 3 gider
            for ($i=0; $i<3; $i++) {
                Transaction::create([
                    'type' => 'expense',
                    'title' => $faker->randomElement($titlesExpense) . " ({$cur})",
                    'amount' => $faker->randomFloat(2, 80, 1500),
                    'currency' => $cur,
                    'transaction_date' => $faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
                    'payment_method' => 'seed',
                    'status' => 'paid',
                    'notes' => 'Seeder gider kaydı',
                    'created_by' => null,
                ]);
            }
        }
    }
    
    private function createGuides($faker, $count): array
    {
        $firstNames = ['Ayşe', 'Fatma', 'Zeynep', 'Elif', 'Meryem', 'Büşra', 'Seda', 'Şeyma', 'Ahmet', 'Mehmet'];
        $lastNames = ['Yılmaz', 'Kaya', 'Demir', 'Şahin', 'Çelik', 'Özkan', 'Arslan', 'Doğan', 'Kılıç', 'Aslan'];
        $nationalities = ['TR', 'DE', 'RU', 'EN', 'FR', 'ES', 'IT'];
        
        $guides = [];
        for ($i = 1; $i <= $count; $i++) {
            $firstName = $faker->randomElement($firstNames);
            $lastName = $faker->randomElement($lastNames);
            
            $guide = Guide::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '.rehber' . $i . '@example.com'),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'license_number' => $faker->regexify('RH[0-9]{6}'),
                'license_expiry' => $faker->dateTimeBetween('now', '+5 years'),
                'supported_nationalities' => $faker->randomElements($nationalities, $faker->numberBetween(3, 5)),
                'status' => $faker->randomElement(['Aktif', 'İzinli', 'Servis Dışı']),
                'hire_date' => $faker->dateTimeBetween('-3 years', 'now'),
                'salary' => $faker->numberBetween(10000, 18000),
                'notes' => $faker->optional(0.3)->sentence,
            ]);
            
            $guides[] = $guide;
        }
        
        return $guides;
    }
    
    private function assignDriversToGuides($drivers, $guides): void
    {
        $shuffledDrivers = collect($drivers)->shuffle();
        $shuffledGuides = collect($guides)->shuffle();
        
        // İlk 10 şoförü rehberlere ata, geri kalanını boş bırak
        $driversToAssign = $shuffledDrivers->take(10);
        
        foreach ($driversToAssign as $index => $driver) {
            $guide = $shuffledGuides[$index % count($guides)]; // Rehberleri döngüyle ata
            
            $driver->guide_id = $guide->id;
            $driver->save();
        }
    }
}