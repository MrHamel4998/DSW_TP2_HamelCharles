<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserInterface
{
    public function getAll(): Collection;

    public function findByLogin(string $login): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): User;

    public function delete(int $id);

    public function updatePassword(int $id, string $hashedPassword): User;
}
