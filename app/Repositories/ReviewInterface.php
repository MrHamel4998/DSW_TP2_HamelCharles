<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Support\Collection;

interface ReviewInterface
{
    public function getAll(): Collection;

    public function findByIdOrFail(int $id): Review;

    public function create(array $data): Review;

    public function update(int $id, array $data): Review;

    public function delete(int $id);

    public function existsForRentalAndUser(int $rentalId, int $userId): bool;
}
