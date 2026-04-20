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

        $response = $this->putJson('/api/users/' . $user->id . '/password', [
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Password updated successfully.']);
        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
    }

    public function test_user_cannot_update_another_users_password(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        $otherUser = User::factory()->create([
            'roleId' => 1,
            'password' => bcrypt('Password123!'),
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/users/' . $otherUser->id . '/password', [
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Forbidden. You can only update your own password.']);
    }

    public function test_user_cannot_update_password_without_authentication(): void
    {
        $this->seed();

        $response = $this->putJson('/api/users/1/password', [
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

        $response = $this->putJson('/api/users/' . $user->id . '/password', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }
}
