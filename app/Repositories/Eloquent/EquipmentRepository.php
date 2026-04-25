<?php

namespace App\Repositories\Eloquent;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Review;
use App\Repositories\EquipmentInterface;
use Illuminate\Support\Collection;

class EquipmentRepository implements EquipmentInterface
{
    public function getAll(): Collection
    {
        return Equipment::all();
    }

    public function findByIdOrFail(int $id): Equipment
    {
        return Equipment::findOrFail($id);
    }

    public function create(array $data): Equipment
    {
        return Equipment::create($data);
    }

    public function update(int $id, array $data): Equipment
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->update($data);

        return $equipment->fresh();
    }

    public function delete(int $id)
    {
        $equipment = Equipment::findOrFail($id);

        return $equipment->delete();
    }

    public function hasRentals(int $id): bool
    {
        $equipment = Equipment::findOrFail($id);

        return $equipment->rentals()->exists();
    }

    public function detachSports(int $id): void
    {
        // https://laravel.com/docs/10.x/eloquent-relationships#attaching-detaching
        $equipment = Equipment::findOrFail($id);
        $equipment->sports()->detach();
    }

    public function calculatePopularity(int $id): float
    {
        $rentalCount = Rental::query()->where('equipment_id', $id)->count();

        $averageReview = Review::query()
            ->join('rentals', 'reviews.rental_id', '=', 'rentals.id')
            ->where('rentals.equipment_id', $id)
            ->avg('reviews.rating');

        $averageReview = $averageReview ?? 0;

        return (float) (($rentalCount * 0.6) + ($averageReview * 0.4));
    }

    public function calculateAverageRentalPrice(int $id, ?string $minDate = null, ?string $maxDate = null): float
    {
        $query = Rental::query()->where('equipment_id', $id);

        if ($minDate !== null) {
            $query->whereDate('start_date', '>=', $minDate);
        }

        if ($maxDate !== null) {
            $query->whereDate('end_date', '<=', $maxDate);
        }

        return (float) ($query->avg('total_price') ?? 0);
    }
}
