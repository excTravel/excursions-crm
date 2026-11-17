<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed only the Russian location/category reference lists and local
        // development accounts. Bookable tours are entered by verified guides.
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            LocationSeeder::class,
            TestUsersSeeder::class,
        ]);

    }
}
