<?php

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
use App\Models\User;

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

    public function updatePassword(UpdatePasswordRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $authUser = $request->user();

        if (!$authUser) {
            abort(401, 'Unauthenticated.');
        }

        if ($authUser->getAuthIdentifier() != $user->id) {
            abort(403, 'Forbidden. You can only update your own password.');
        }

        $data = $request->validated();

        $this->userRepository->updatePassword($id, Hash::make($data['password']));

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}