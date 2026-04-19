<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RentalController extends Controller
{
	public function activeRentals(Request $request): JsonResponse
	{
        // Aide de ChatGPT pour la requête.
        // Prompt : "Ecris une requête Eloquent pour récupérer les locations actives d'un utilisateur,
        // c'est à dire celles dont la date de début est inférieure ou égale à aujourd'hui et la date de fin
        // est supérieure ou égale à aujourd'hui. Trie les résultats par date de début croissante."
        $today = Carbon::today()->toDateString();

		$rentals = Rental::query()
			->where('user_id', $request->user()->id)
			->where('start_date', '<=', $today)
			->where('end_date', '>=', $today)
			->orderBy('start_date', 'asc')
			->get();

		return response()->json([
			'data' => $rentals,
		], 200);
	}
}
