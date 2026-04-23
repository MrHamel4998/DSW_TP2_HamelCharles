<?php
/**
 * Documentation Swagger (OpenAPI) générée avec l'assistance de GitHub Copilot (GPT-5.3-Codex).
 *
 * Motif :
 * - Accélérer la production des annotations
 * - Assurer une structure conforme aux standards OpenAPI
 * - Réduire les erreurs de syntaxe répétitives
 *
 * Limites :
 * - Les annotations ont été validées manuellement (routes, schémas, sécurité)
 * - Le throttling documenté a été ajouté par l'étudiant
 *
 * Responsabilité :
 * - Le contenu final a été relu, ajusté et intégré dans le projet
 * - Les tests via Swagger UI ont été effectués pour valider le comportement
 */

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private UserInterface $userRepository)
    {
    }

    #[OA\Post(
        path: '/api/users',
        summary: 'Créer un utilisateur',
        description: 'Un nouvel utilisateur en paramètre',
        tags: ['User'],
        requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['first_name', 'last_name', 'email', 'phone'],
                    properties: [
                        new OA\Property(property: 'first_name', type: 'string', example: 'Jean'),
                        new OA\Property(property: 'last_name', type: 'string', example: 'Dupont'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jean@email.com'),
                        new OA\Property(property: 'phone', type: 'string', example: '819-789-1234')
                    ]
                )
            ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur créé'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation échouée'
            )
        ]
    )]
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userRepository->create($request->validated());
            return (new UserResource($user))->response()->setStatusCode(201);
        } catch (QueryException $ex) {
            abort(422, 'UserController/Cannot be created');
        } catch (Exception $ex) {
            abort (500, 'UserController/Server error');
        }
    }

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Mettre à jour d\'un utilisateur (mise à jour complète et non partielle)',
        description: 'Utilisateur et id en paramètres',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
            )
        ],
        requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['first_name', 'last_name', 'email', 'phone'],
                    properties: [
                        new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                        new OA\Property(property: 'last_name', type: 'string', example: 'Smith'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'John@email.com'),
                        new OA\Property(property: 'phone', type: 'string', example: '819-789-4567')
                    ]
                )
            ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur modifié',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'first_name', type: 'string'),
                        new OA\Property(property: 'last_name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'phone', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    public function update(StoreUserRequest $request, int $id)
    {
        try {
            $validated = $request->validated();
            $user = $this->userRepository->update($id, $validated);

            return (new UserResource($user))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $ex) {
            abort(404, 'UserController/ID Not Found');
        } catch (ValidationException $ex) {
            abort (422, 'UserController/Failed validation');
        } catch (QueryException $ex) {
            abort (422, 'UserController/Cannot be updated in database');
        } catch (Exception $ex) {
            abort (500, 'UserController/Server error');        
        }
    }

    #[OA\Patch(
        path: '/api/user/password',
        summary: 'Mettre à jour son mot de passe',
        description: 'Un utilisateur ne peut modifier que son propre mot de passe. Authentification requise. Throttling: 60 requêtes/minute.',
        tags: ['User'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'password', type: 'string', example: 'NewPassword456!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'NewPassword456!')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe mis à jour'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 422, description: 'Validation échouée')
        ]
    )]
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $authUser = $request->user();

        if (!$authUser) {
            abort(401, 'Unauthenticated.');
        }

        $data = $request->validated();

        $this->userRepository->updatePassword($authUser->id, Hash::make($data['password']));

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}