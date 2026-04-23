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
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Repositories\EquipmentInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct(private EquipmentInterface $equipmentRepository)
    {
    }

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
            $this->equipmentRepository->getAll()
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
    public function show(int $id){
        try {
            return ( new EquipmentResource(
                $this->equipmentRepository->findByIdOrFail($id))
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
public function calculatePopularity(int $id)
{
    try {
        $popularity = $this->equipmentRepository->calculatePopularity($id);

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
    public function calculateAverageRentalPrice(Request $request, int $id) {
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

            $average = $this->equipmentRepository->calculateAverageRentalPrice(
                $id,
                $minDate,
                $maxDate
            );
            return response()->json([
                'average_total_price' => $average ?? 0
            ], 200);

        } catch (ModelNotFoundException $ex) {
            abort(404, 'EquipmentController/ID Not Found');
        } catch (Exception $ex) {
            abort(500, 'EquipmentController/Server Error');
        }
    }

    #[OA\Post(
        path: '/api/equipment',
        summary: 'Ajouter un équipement',
        description: 'Seulement si admin. Throttling: 60 requêtes/minute.',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'description', 'daily_price', 'category_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Surfboard'),
                    new OA\Property(property: 'description', type: 'string', example: 'Planche de surf'),
                    new OA\Property(property: 'daily_price', type: 'number', format: 'float', example: 30),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Équipement créé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Interdit: rôle admin requis'),
            new OA\Response(response: 422, description: 'Validation échouée')
        ]
    )]
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $equipment = $this->equipmentRepository->create($data);

        return response()->json([
            'message' => 'Equipment created successfully.',
            'data' => $equipment,
        ], 201);
    }

    #[OA\Put(
        path: '/api/equipment/{id}',
        summary: 'Mettre à jour un équipement (global)',
        description: 'Mise à jour complète. Seulement si admin. Throttling: 60 requêtes/minute.',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true)
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'description', 'daily_price', 'category_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Surfboard Pro'),
                    new OA\Property(property: 'description', type: 'string', example: 'Planche de surf pro'),
                    new OA\Property(property: 'daily_price', type: 'number', format: 'float', example: 45),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Équipement mis à jour'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Interdit: rôle admin requis'),
            new OA\Response(response: 404, description: 'Équipement non trouvé'),
            new OA\Response(response: 422, description: 'Validation échouée')
        ]
    )]
    public function update(UpdateEquipmentRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $equipment = $this->equipmentRepository->update($id, $data);

        return response()->json([
            'message' => 'Equipment updated successfully.',
            'data' => $equipment,
        ], 200);
    }

    #[OA\Delete(
        path: '/api/equipment/{id}',
        summary: 'Supprimer un équipement',
        description: 'Seulement si admin. Throttling: 60 requêtes/minute.',
        tags: ['Equipment'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true)
        ],
        responses: [
            new OA\Response(response: 204, description: 'Équipement supprimé'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Interdit: rôle admin requis'),
            new OA\Response(response: 404, description: 'Équipement non trouvé'),
            new OA\Response(response: 409, description: 'Conflit: équipement lié à des locations')
        ]
    )]
    public function destroy(int $id)
    {
        $equipment = $this->equipmentRepository->findByIdOrFail($id);

        if ($this->equipmentRepository->hasRentals($id)) {
            abort(409, 'Cannot delete equipment that is linked to rentals.');
        }

        $this->equipmentRepository->detachSports($id);
        $this->equipmentRepository->delete($id);

        return response()->noContent(204);
    }

}