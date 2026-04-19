<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Models\Review;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class ReviewController extends Controller
{
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
