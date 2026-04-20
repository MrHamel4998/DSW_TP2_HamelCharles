<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RentalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_active_rentals(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();

        $activeRental = Rental::factory()->create([
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'start_date' => Carbon::today()->subDay(), // Utilisation de l'IA pour savoir comment faire les dates
            'end_date' => Carbon::today()->addDay(),
        ]);

        Rental::factory()->create([
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->subDays(5),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/rentals/');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $activeRental->id]);
    }

    public function test_active_rentals_require_authentication(): void
    {
        $this->seed();

        $response = $this->getJson('/api/rentals/');

        $response->assertStatus(401);
    }

    public function test_active_rentals_are_throttled_after_sixty_requests(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);

        Sanctum::actingAs($user);

        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/rentals/');
        }

        $response->assertStatus(429);
    }
}
