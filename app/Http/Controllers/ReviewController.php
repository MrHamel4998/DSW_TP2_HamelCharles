<?php

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
