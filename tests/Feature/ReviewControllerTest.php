<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_one_review_per_rental(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();
        $rental = Rental::factory()->create([
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 5,
            'comment' => 'Excellent équipement.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Review created successfully.']);

        $duplicate = $this->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 4,
            'comment' => 'Deuxième avis.',
        ]);

        $duplicate->assertStatus(422);
        $duplicate->assertJsonFragment(['message' => 'A review already exists for this rental and user.']);
        $this->assertEquals(1, Review::query()->where('rental_id', $rental->id)->where('user_id', $user->id)->count());
    }

// Aide de ChatGPT pour les tests d'authentification et d'autorisation.
// Prompt : "Ecris un tests PHPUnit pour vérifier que les utilisateurs ne peuvent pas créer de
// avis pour des locations qui ne leur appartiennent pas, et que l'authentification est requise pour créer un avis."
    public function test_user_cannot_review_someone_elses_rental(): void
    {
        $this->seed();

        $owner = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $otherUser = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();
        $rental = Rental::factory()->create([
            'user_id' => $owner->id,
            'equipment_id' => $equipment->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/reviews', [
            'rental_id' => $rental->id,
            'rating' => 5,
            'comment' => 'Pas mon rental.',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Forbidden. You can only review your own rentals.']);
    }

    public function test_create_review_requires_authentication(): void
    {
        $this->seed();

        $response = $this->postJson('/api/reviews', [
            'rental_id' => 1,
            'rating' => 5,
            'comment' => 'Excellent équipement.',
        ]);

        $response->assertStatus(401);
    }
}
