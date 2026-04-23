<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EquipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_equipment(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/equipment', [
            'name' => 'Surfboard',
            'description' => 'Planche de surf',
            'daily_price' => 30.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Equipment created successfully.']);
        $this->assertDatabaseHas('equipment', ['name' => 'Surfboard']);
    }

    public function test_non_admin_cannot_create_equipment(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/equipment', [
            'name' => 'Surfboard',
            'description' => 'Planche de surf',
            'daily_price' => 30.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Forbidden. Admin role required.']);
    }

    public function test_create_equipment_requires_authentication(): void
    {
        $this->seed();

        $response = $this->postJson('/api/equipment', [
            'name' => 'Surfboard',
            'description' => 'Planche de surf',
            'daily_price' => 30.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_update_equipment(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/equipment/' . $equipment->id, [
            'name' => 'Surfboard Pro',
            'description' => 'Planche de surf pro',
            'daily_price' => 45.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Equipment updated successfully.']);
        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'name' => 'Surfboard Pro',
        ]);
    }

    public function test_non_admin_cannot_update_equipment(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/equipment/' . $equipment->id, [
            'name' => 'Surfboard Pro',
            'description' => 'Planche de surf pro',
            'daily_price' => 45.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_equipment_without_rentals(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);

        Sanctum::actingAs($admin);

        $create = $this->postJson('/api/equipment', [
            'name' => 'Equipment sans rental',
            'description' => 'Test',
            'daily_price' => 22.00,
            'category_id' => 1,
        ]);

        $create->assertStatus(201);
        $equipmentId = $create->json('data.id');

        $response = $this->deleteJson('/api/equipment/' . $equipmentId);

        $response->assertNoContent();
        $this->assertDatabaseMissing('equipment', ['id' => $equipmentId]);
    }

    public function test_admin_cannot_delete_equipment_with_rentals(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);
        $equipmentId = Rental::query()->firstOrFail()->equipment_id;

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/equipment/' . $equipmentId);

        $response->assertStatus(409);
        $response->assertJsonFragment(['message' => 'Cannot delete equipment that is linked to rentals.']);
    }

    public function test_create_equipment_is_throttled_after_sixty_requests(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);

        Sanctum::actingAs($admin);

        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('/api/equipment', [
                'name' => 'Surfboard ' . $i,
                'description' => 'Planche de surf',
                'daily_price' => 30.00,
                'category_id' => 1,
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_update_equipment_is_throttled_after_sixty_requests(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);
        $equipment = Equipment::query()->firstOrFail();

        Sanctum::actingAs($admin);

        for ($i = 0; $i < 61; $i++) {
            $response = $this->putJson('/api/equipment/' . $equipment->id, [
                'name' => 'Surfboard Pro',
                'description' => 'Planche de surf pro',
                'daily_price' => 45.00,
                'category_id' => 1,
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_delete_equipment_is_throttled_after_sixty_requests(): void
    {
        $this->seed();

        $admin = User::factory()->create([
            'roleId' => 2,
            'password' => bcrypt('Password123!'),
        ]);
        $equipmentId = Rental::query()->firstOrFail()->equipment_id;

        Sanctum::actingAs($admin);

        for ($i = 0; $i < 61; $i++) {
            $response = $this->deleteJson('/api/equipment/' . $equipmentId);
        }

        $response->assertStatus(429);
    }
}
