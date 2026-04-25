<?php

namespace App\Repositories;

use App\Models\Sport;
use Illuminate\Support\Collection;

interface SportInterface
{
    public function getAll(): Collection;

    public function findByIdOrFail(int $id): Sport;

    public function create(array $data): Sport;

    public function update(int $id, array $data): Sport;

    public function delete(int $id);
}
