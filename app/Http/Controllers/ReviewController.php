<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Models\Rental;
use App\Models\Review;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'rental_id' => 'required|integer|exists:rentals,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rental = Rental::findOrFail($data['rental_id']);
        $rental->loadMissing('user');

        if (! $user || ! $user->is($rental->user)) {
            abort(403, 'Forbidden. You can only review your own rentals.');
        }

        $alreadyExists = Review::where('rental_id', $data['rental_id'])
            ->where('user_id', $user->id)->exists();

        if ($alreadyExists) {
            abort(422, 'A review already exists for this rental and user.');
        }

        $review = Review::create([
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
            $review = Review::findOrFail($id);
            $review->delete();
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
