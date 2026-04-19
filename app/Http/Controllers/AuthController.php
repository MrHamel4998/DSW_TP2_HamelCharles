<?php
/**
 * Documentation Swagger (OpenAPI) générée avec l'assistance de ChatGPT.
 *
 * Motif :
 * - Accélérer la production des annotations
 * - Assurer une structure conforme aux standards OpenAPI
 * - Réduire les erreurs de syntaxe répétitives
 *
 * Limites :
 * - Les annotations doivent ont étés validées manuellement (routes, schémas, sécurité)
 * - Le throttling documenté a été ajouté par l'étudiant
 *
 * Responsabilité :
 * - Le contenu final a été relu, ajusté et intégré dans le projet
 * - Les tests via Swagger UI ont été effectués pour valider le comportement
 */


namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
#[OA\Post(
    path: '/api/signup',
    summary: 'Créer un utilisateur',
    description: 'Création d\'un utilisateur. Throttling: 5 requêtes/minute',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['first_name','last_name','email','login','password','password_confirmation'],
            properties: [
                new OA\Property(property: 'roleId', type: 'integer', nullable: true, example: 1),
                new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                new OA\Property(property: 'email', type: 'string', example: 'john@email.com'),
                new OA\Property(property: 'login', type: 'string', example: 'johndoe'),
                new OA\Property(property: 'password', type: 'string', example: 'Password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'Password123'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Utilisateur créé'),
        new OA\Response(response: 422, description: 'Validation échouée')
    ]
)]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $user->load('role');

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    #[OA\Post(
    path: '/api/signin',
    summary: 'Connexion',
    description: 'Authentifie et retourne un token. Throttling: 5 requêtes/minute',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['login','password'],
            properties: [
                new OA\Property(property: 'login', type: 'string', example: 'johndoe'),
                new OA\Property(property: 'password', type: 'string', example: 'Password123'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Succès'),
        new OA\Response(response: 401, description: 'Identifiants invalides'),
        new OA\Response(response: 422, description: 'Validation échouée')
    ]
)]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login or password.',
            ], 401);
        }

        $user = auth()->user();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token');
        $user->load('role');

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
            ],
        ], 200);
    }

    #[OA\Get(
    path: '/api/me',
    summary: 'Utilisateur courant',
    description: 'Retourne l\'utilisateur connecté. Throttling: 5 requêtes/minute',
    tags: ['Auth'],
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(response: 200, description: 'OK'),
        new OA\Response(response: 401, description: 'Non autorisé')
    ]
)]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'data' => new UserResource($user),
        ], 200);
    }

    #[OA\Post(
    path: '/api/refresh',
    summary: 'Refresh token',
    description: 'Génère un nouveau token. Throttling: 5 requêtes/minute',
    tags: ['Auth'],
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Token rafraîchi'),
        new OA\Response(response: 401, description: 'Non autorisé')
    ]
)]
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();
        } else {
            $user->tokens()->delete();
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('role');

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 200);
    }

    #[OA\Post(
    path: '/api/signout',
    summary: 'Déconnexion',
    description: 'Supprime le token courant. Throttling: 5 requêtes/minute',
    tags: ['Auth'],
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(response: 204, description: 'Déconnecté'),
        new OA\Response(response: 401, description: 'Non autorisé')
    ]
)]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 204);
    }
}
