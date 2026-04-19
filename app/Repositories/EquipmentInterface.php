<?php

namespace App\Repositories;

use App\Models\Equipment;
use Illuminate\Support\Collection;

interface EquipmentInterface
{
    public function getAll(): Collection;

    public function findByIdOrFail(int $id): Equipment;

    public function create(array $data): Equipment;

    public function update(int $id, array $data): Equipment;

    public function delete(int $id);

    public function hasRentals(int $id): bool;

    public function detachSports(int $id): void;

    public function calculatePopularity(int $id): float;

    public function calculateAverageRentalPrice(int $id, ?string $minDate = null, ?string $maxDate = null): float;
}
