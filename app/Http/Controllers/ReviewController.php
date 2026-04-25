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
use App\Http\Requests\StoreReviewRequest;
use App\Repositories\RentalInterface;
use App\Repositories\ReviewInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(
        private RentalInterface $rentalRepository,
        private ReviewInterface $reviewRepository
    ) {
    }

    #[OA\Post(
        path: '/api/reviews',
        summary: 'Créer une critique pour une location',
        description: 'Une seule critique par location pour un utilisateur. Authentification requise. Throttling: 60 requêtes/minute.',
        tags: ['Review'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rental_id', 'rating'],
                properties: [
                    new OA\Property(property: 'rental_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rating', type: 'integer', example: 5),
                    new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Excellent équipement.')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Critique créée'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Interdit: location d\'un autre utilisateur'),
            new OA\Response(response: 422, description: 'Validation échouée ou critique déjà existante')
        ]
    )]
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $rental = $this->rentalRepository->findByIdOrFail((int) $data['rental_id']);
        $rental->loadMissing('user');

        if (! $user || ! $user->is($rental->user)) {
            abort(403, 'Forbidden. You can only review your own rentals.');
        }

        $alreadyExists = $this->reviewRepository->existsForRentalAndUser(
            $data['rental_id'],
            $user->id
        );

        if ($alreadyExists) {
            abort(422, 'A review already exists for this rental and user.');
        }

        $review = $this->reviewRepository->create([
            'rental_id' => $data['rental_id'],
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Review created successfully.',
            'data' => $review,
        ], 201);
    }

    #[OA\Delete(
        path: '/api/reviews/{id}',
        summary: 'Supprimer une critique par ID',
        description: 'Supprimer une critique',
        tags: ['Review'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Critique supprimé',
            ),
            new OA\Response(
                response: 404,
                description: 'Critique non trouvée'
            )
        ]
    )]
    public function destroy(string $id) {
        try {
            $this->reviewRepository->delete($id);
            return response()->noContent(204);
        } catch ( ModelNotFoundException $ex) {
            abort (404, 'ReviewController/Id not Found');
        } catch (QueryException $ex) {
            abort (500, 'ReviewController/Database error');
        } catch (Exception $ex) {
            abort (500, 'ReviewController/Server error');
        }
    }
}
