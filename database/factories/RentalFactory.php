<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Equipment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $start = fake()->dateTimeBetween('-1 year', 'now');
        $days = fake()->numberBetween(1, 14);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'equipment_id' => fake()->numberBetween(1, 5),
            'start_date' => $start,
            'end_date' => (clone $start)->modify("+$days days"),
            'total_price' => $days * fake()->numberBetween(5, 10)
        ];
    }
}
