<?php

namespace App\Repositories\Eloquent;

use App\Models\Sport;
use App\Repositories\SportInterface;
use Illuminate\Support\Collection;

class SportRepository implements SportInterface
{
    public function getAll(): Collection
    {
        return Sport::all();
    }

    public function findByIdOrFail(int $id): Sport
    {
        return Sport::findOrFail($id);
    }

    public function create(array $data): Sport
    {
        return Sport::create($data);
    }

    public function update(int $id, array $data): Sport
    {
        $sport = Sport::findOrFail($id);
        $sport->update($data);

        return $sport->fresh();
    }

    public function delete(int $id)
    {
        $sport = Sport::findOrFail($id);

        return $sport->delete();
    }
}
