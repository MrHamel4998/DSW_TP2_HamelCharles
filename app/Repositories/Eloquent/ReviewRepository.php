<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\ReviewInterface;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewInterface
{
    public function getAll(): Collection
    {
        return Review::all();
    }

    public function findByIdOrFail(int $id): Review
    {
        return Review::findOrFail($id);
    }

    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function update(int $id, array $data): Review
    {
        $review = Review::findOrFail($id);
        $review->update($data);

        return $review->fresh();
    }

    public function delete(int $id)
    {
        $review = Review::findOrFail($id);

        return $review->delete();
    }

    public function existsForRentalAndUser(int $rentalId, int $userId): bool
    {
        return Review::query()
            ->where('rental_id', $rentalId)
            ->where('user_id', $userId)
            ->exists();
    }
}
