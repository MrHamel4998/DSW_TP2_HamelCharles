<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    #[OA\Get(
        path: '/api/equipments',
        summary: 'Récupérer la liste des équipements',
        description: 'Retourner tous les équipements existants',
        tags: ['Equipment'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des équipements récupérée avec succès',
                content: [
                    new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'description', type: 'string'),
                            new OA\Property(property: 'daily_price', type: 'number', format: 'float'),
                            new OA\Property(property: 'categoryId', type: 'integer', nullable: true)
                        ]
                    )
                ]
            )
        ]
    )]
    public function index()
    {
        try {
            return EquipmentResource::collection(
            Equipment::all()
            )->response()->setStatusCode(200);
        } catch (Exception $ex) {
            abort (500, 'EquipmentController/Server Error');
        }
    }

    #[OA\Get(
        path: '/api/equipments/{id}',
        summary: 'Récupérer un équipement par ID',
        description: 'Retourner les détails d\'un équipement',
        tags: ['Equipment'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Équipement trouvé',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'daily_price', type: 'number', format: 'float'),
                        new OA\Property(property: 'categoryId', type: 'integer', nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Équipement non trouvé')
        ]
    )]
    public function show(string $id){
        try {
            return ( new EquipmentResource(
                Equipment::findOrFail($id))
                )->response()->setStatusCode(200);
        } catch (ModelNotFoundException $ex) {
            abort(404, 'EquipmentController/ID Not Found');
        } catch (Exception $ex) {
            abort (500, 'EquipmentController/Server Error');
        }
    }


    #[OA\Get(
        path: '/api/equipments/{id}/popularity',
        summary: 'Récupérer la popularité d\'un équipement par ID',
        description: 'Retourner la popularité d\'un équipement',
        tags: ['Equipment'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Popularité trouvé',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'popularity', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Popularité non trouvé')
        ]
    )]
public function calculatePopularity($id)
{
    try {
        $rentalCount = DB::table('rentals')->where('equipment_id', $id)->count();
        $averageReview = DB::table('reviews')->where('equipment_id', $id)->avg('rating');

        if ($averageReview === null) {
            $averageReview = 0;
        }

        $popularity = ($rentalCount * 0.6) + ($averageReview * 0.4);

        return response()->json([
            'popularity' => $popularity
        ], 200);

    } catch (ModelNotFoundException $ex) {
        abort(404, 'EquipmentController/ID Not Found');
    } catch (Exception $ex) {
        abort(500, 'EquipmentController/Server Error');
    }
}

    #[OA\Get(
        path: '/api/equipments/{id}/average-rental-price',
        summary: 'Recevoir la moyenne du prix total de location d\'un équipement',
        description: 'minDate et maxDate pour filtrer les locations',
        tags: ['Equipment'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
            ),
            new OA\Parameter(
                name: 'minDate',
                in: 'query',
                required: false,
                description: 'AAAA-MM-JJ',
            ),
            new OA\Parameter(
                name: 'maxDate',
                in: 'query',
                required: false,
                description: 'AAAA-MM-JJ',
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Moyenne calculée',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'average_total_price', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format de date invalide ou minDate > maxDate'),
            new OA\Response(response: 404, description: 'Équipement non trouvé')
        ]
    )]
// Utilisation de ChatGPT:
// Prompt : Comment on fait pour savoir si une date à un format valide en php
// ChatGPT : Pour transformer une chaîne de caractères représentant une date en valeur exploitable en PHP, on utilise strtotime(). 
//           strtotime() sert à convertir une date texte en timestamp, si la conversion échoue, il retourne false..
    public function calculateAverageRentalPrice(Request $request, $id) {
        try 
        {
            $minDate = $request->query('minDate');
            $maxDate = $request->query('maxDate');

            if ($minDate != null && !strtotime($minDate)) {
                abort(422, 'Format minDate invalide');
            }

            if ($maxDate != null && !strtotime($maxDate)) {
                abort(422, 'Format maxDate invalide');
            }

            if ($minDate && $maxDate && strtotime($minDate) > strtotime($maxDate)) {
                abort(422, 'minDate doit être inférieur à maxDate.');
            }

            $query = DB::table('rentals')->where('equipment_id', $id);

            if ($minDate != null) {
                $query->whereDate('start_date', '>=', $minDate);
            }

            if ($maxDate != null) {
                $query->whereDate('end_date', '<=', $maxDate);
            }

            $average = $query->avg('total_price');
            return response()->json([
                'average_total_price' => $average ?? 0
            ], 200);

        } catch (ModelNotFoundException $ex) {
            abort(404, 'EquipmentController/ID Not Found');
        } catch (Exception $ex) {
            abort(500, 'EquipmentController/Server Error');
        }
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $equipment = Equipment::create($data);

        return response()->json([
            'message' => 'Equipment created successfully.',
            'data' => $equipment,
        ], 201);
    }

    public function update(UpdateEquipmentRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $equipment = Equipment::findOrFail($id);

        $equipment->update($data);

        return response()->json([
            'message' => 'Equipment updated successfully.',
            'data' => $equipment,
        ], 200);
    }

    public function destroy(string $id)
    {
        $equipment = Equipment::findOrFail($id);

        if ($equipment->rentals()->exists()) {
            abort(409, 'Cannot delete equipment that is linked to rentals.');
        }

        // Détacher les sports associés avant de supprimer l'équipement
        // https://laravel.com/docs/10.x/eloquent-relationships#attaching-detaching
        $equipment->sports()->detach();

        $equipment->delete();

        return response()->noContent(204);
    }

}