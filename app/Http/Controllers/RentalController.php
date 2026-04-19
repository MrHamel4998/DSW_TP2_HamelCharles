<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RentalController extends Controller
{
    public function active(Request $request): JsonResponse
    {
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
