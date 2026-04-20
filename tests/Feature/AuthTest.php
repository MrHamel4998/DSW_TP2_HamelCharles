<?php
/**
 * Les cas de tests ont été élaborés avec l'assistance de l'IA (brainstorming),
 * dans l'objectif d'assurer une couverture exhaustive fonctionnelle, large et structurée.
 *
 * Portée des tests :
 * - Authentification : succès, échecs, gestion des sessions
 * - Limitation de requêtes (throttling) : seuils, blocages, comportements attendus
 * - Validation des entrées : champs requis, formats, contraintes de sécurité
 * - Sécurité : vérification du hashage des mots de passe
 * - Réponses API : structure et présence des données critiques (ex: token)
 *
 * Détail des cas couverts :
 * - Inscription réussie
 * - Connexion réussie
 * - Connexion refusée avec identifiants invalides
 * - Déconnexion nécessite authentification
 * - Déconnexion réussie
 * - Inscription bloquée à la 6e tentative (throttling)
 * - Inscription autorisée jusqu’à 60 tentatives
 * - Connexion bloquée à la 61e tentative
 * - Connexion autorisée jusqu’à 60 tentatives (erreur identifiants attendue)
 * - Déconnexion bloquée à la 61e tentative
 * - Déconnexion autorisée jusqu’à 60 tentatives
 * - Validation inscription : prénom, nom, email, login, mot de passe manquants
 * - Validation inscription : email invalide
 * - Validation inscription : mot de passe trop court / sans chiffre / sans lettre
 * - Validation inscription : confirmation non correspondante
 * - Validation inscription : email et login déjà utilisés
 * - Sécurité : mot de passe stocké hashé
 * - Validation connexion : login ou mot de passe manquant
 * - Réponse connexion : présence du token
 *
 * Limite :
 * - Les cas de tests ont été générés par l'étudiant et ont été validés 
 * manuellement pour s'assurer de leur pertinence avec les exigences du TP.
 * 
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => "User created successfully."]);
        $response->assertJsonFragment(['email' => $json['email']]);
        $response->assertJsonFragment(['login' => $json['login']]);
        $this->assertDatabaseHas('users', ['email' => $json['email']]);
        $this->assertDatabaseHas('users', ['login' => $json['login']]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        $json = [
            'login' => $user->login,
            'password' => 'Password123!',
        ];

        $response = $this->postJson('api/signin', $json);
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Login successful.']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        $json = [
            'login' => $user->login,
            'password' => 'WrongPassword123!',
        ];

        $response = $this->postJson('/api/signin', $json);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Invalid login or password.']);
    }

    public function test_signout_requires_authentication(): void
    {
        $response = $this->postJson('/api/signout');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/signout');

        $response->assertStatus(204);
    }

    public function test_signup_sixty_one_times(): void
    {
        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('api/signup', [
                'first_name' => 'John' . $i,
                'last_name' => 'Doe' . $i,
                'email' => 'john' . $i . '.doe@example.com',
                'login' => 'johndoe' . $i,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_signup_sixty_times(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $response = $this->postJson('api/signup', [
                'first_name' => 'John' . $i,
                'last_name' => 'Doe' . $i,
                'email' => 'john' . $i . '.doe@example.com',
                'login' => 'johndoe' . $i,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);
        }

        $response->assertStatus(201);
    }

    public function test_signin_sixty_one_times(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        for ($i = 0; $i < 61; $i++) {
            $response = $this
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->postJson('api/signin', [
                'login' => $user->login,
                'password' => 'WrongPassword123!',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_signin_sixty_times(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        for ($i = 0; $i < 60; $i++) {
            $response = $this
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->postJson('api/signin', [
                'login' => $user->login,
                'password' => 'WrongPassword123!',
            ]);
        }

        $response->assertStatus(401);
    }

    public function test_signout_sixty_one_times(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('/api/signout');
        }

        $response->assertStatus(429);
    }

    public function test_signout_sixty_times(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 60; $i++) {
            $response = $this->postJson('/api/signout');
        }

        $response->assertStatus(204);
    }

    public function test_register_with_missing_first_name(): void
    {
        $json = [
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_missing_last_name(): void
    {
        $json = [
            'first_name' => 'John',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_missing_email(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_missing_login(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_missing_password(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_invalid_email(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_password_too_short(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'Pass1',
            'password_confirmation' => 'Pass1',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_password_without_number(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'PasswordTest!',
            'password_confirmation' => 'PasswordTest!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_password_without_letter(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => '1234567890',
            'password_confirmation' => '1234567890',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_mismatched_password_confirmation(): void
    {
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'login' => 'johndoe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_with_duplicate_login(): void
    {
        User::factory()->create(['login' => 'existinglogin']);

        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'existinglogin',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('api/signup', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_password_is_hashed(): void
    {
        $plainPassword = 'Password123!';
        $json = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'login' => 'johndoe',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
        ];

        $this->postJson('api/signup', $json);
        
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    public function test_login_with_missing_login(): void
    {
        User::factory()->create(['password' => bcrypt('Password123!')]);

        $json = [
            'password' => 'Password123!',
        ];

        $response = $this->postJson('api/signin', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_login_with_missing_password(): void
    {
        $user = User::factory()->create();

        $json = [
            'login' => $user->login,
        ];

        $response = $this->postJson('api/signin', $json);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);

        $json = [
            'login' => $user->login,
            'password' => 'Password123!',
        ];

        $response = $this->postJson('api/signin', $json);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'login',
                ],
                'token',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.token'));
    }
}

