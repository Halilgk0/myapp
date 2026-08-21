<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // TestDataSeeder::class,  // Eski küçük seeder
            // ExtendedTestDataSeeder::class,  // Eski genişletilmiş seeder (Vehicle model uyumlu değil)
            SimpleExtendedSeeder::class,  // Güncel seeder
            AgencySeeder::class,  // Acenta verileri
        ]);
    }
}