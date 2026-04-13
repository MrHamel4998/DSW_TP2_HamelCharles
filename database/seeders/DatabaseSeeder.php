<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rental;
use App\Models\Review;
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
        $this->call([
            CategorySeeder::class,
            EquipmentSeeder::class,
            SportSeeder::class,
            EquipmentSportSeeder::class,
        ]);

        User::factory(10)->create();

        Rental::factory(80)->create();

    }
}
