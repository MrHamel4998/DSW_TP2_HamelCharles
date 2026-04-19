<?php

namespace App\Repositories\Eloquent;

use App\Models\Rental;
use App\Repositories\RentalInterface;
use Illuminate\Support\Collection;

class RentalRepository implements RentalInterface
{
    public function getAll(): Collection
    {
        return Rental::all();
    }

    public function findByIdOrFail(int $id): Rental
    {
        return Rental::findOrFail($id);
    }

    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function update(int $id, array $data): Rental
    {
        $rental = Rental::findOrFail($id);
        $rental->update($data);

        return $rental->fresh();
    }

    public function delete(int $id)
    {
        $rental = Rental::findOrFail($id);

        return $rental->delete();
    }

    // Aide de ChatGPT pour la requête.
    // Prompt : "Ecris une requête Eloquent pour récupérer les locations actives d'un utilisateur,
    // c'est à dire celles dont la date de début est inférieure ou égale à aujourd'hui et la date de fin
    // est supérieure ou égale à aujourd'hui. Trie les résultats par date de début croissante."
    public function getActiveByUser(int $userId, string $date): Collection
    {
        return Rental::query()
            ->where('user_id', $userId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->orderBy('start_date', 'asc')
            ->get();
    }
}
