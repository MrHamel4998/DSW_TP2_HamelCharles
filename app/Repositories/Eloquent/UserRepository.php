<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\UserInterface;
use Illuminate\Support\Collection;

class UserRepository implements UserInterface
{
    public function getAll(): Collection
    {
        return User::all();
    }

    public function findByLogin(string $login): ?User
    {
        return User::query()->where('login', $login)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);

        return $user->fresh();
    }

    public function delete(int $id)
    {
        $user = User::findOrFail($id);

        return $user->delete();
    }

    public function updatePassword(int $id, string $hashedPassword): User
    {
        $user = User::findOrFail($id);
        $user->password = $hashedPassword;
        $user->save();

        return $user->fresh();
    }
}
