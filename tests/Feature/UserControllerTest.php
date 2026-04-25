<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_password(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user/password', [
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Password updated successfully.']);
        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
    }

    public function test_user_update_password_only_affects_authenticated_user(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $otherUser = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('OtherPassword123!'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user/password', [
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
        $this->assertTrue(Hash::check('OtherPassword123!', $otherUser->fresh()->password));
    }

    public function test_user_cannot_update_password_without_authentication(): void
    {
        $this->seed();

        $response = $this->patchJson('/api/user/password', [
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_password_with_invalid_data(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user/password', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_update_password_is_throttled_after_sixty_requests(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        Sanctum::actingAs($user);

        for ($i = 0; $i < 61; $i++) {
            $password = 'NewPassword' . $i . '456!';

            $response = $this->patchJson('/api/user/password', [
                'password' => $password,
                'password_confirmation' => $password,
            ]);
        }

        $response->assertStatus(429);
    }
}
