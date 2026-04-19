<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Support\Collection;

interface RentalInterface
{
    public function getAll(): Collection;

    public function findByIdOrFail(int $id): Rental;

    public function create(array $data): Rental;

    public function update(int $id, array $data): Rental;

    public function delete(int $id);

    public function getActiveByUser(int $userId, string $date): Collection;
}
