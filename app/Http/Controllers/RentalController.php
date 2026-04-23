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
use App\Repositories\RentalInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RentalController extends Controller
{
	public function __construct(private RentalInterface $rentalRepository)
	{
	}

	#[OA\Get(
		path: '/api/rentals/',
		summary: 'Afficher les locations actives de l\'utilisateur connecté',
		description: 'Accessible uniquement si connecté. Throttling: 60 requêtes/minute.',
		tags: ['Rental'],
		security: [['bearerAuth' => []]],
		responses: [
			new OA\Response(response: 200, description: 'Liste des locations actives'),
			new OA\Response(response: 401, description: 'Non authentifié')
		]
	)]
	public function activeRentals(Request $request): JsonResponse
	{
		$user = $request->user();

        // Aide de ChatGPT pour la requête.
        // Prompt : "Ecris une requête Eloquent pour récupérer les locations actives d'un utilisateur,
        // c'est à dire celles dont la date de début est inférieure ou égale à aujourd'hui et la date de fin
        // est supérieure ou égale à aujourd'hui. Trie les résultats par date de début croissante."
        $today = Carbon::today()->toDateString();

		$rentals = $this->rentalRepository->getActiveByUser($user->id, $today);

		return response()->json([
			'data' => $rentals,
		], 200);
	}
}
