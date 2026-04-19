<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\CategoryInterface;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryInterface
{
    public function getAll(): Collection
    {
        return Category::all();
    }

    public function findByIdOrFail(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category->fresh();
    }

    public function delete(int $id)
    {
        $category = Category::findOrFail($id);

        return $category->delete();
    }
}
