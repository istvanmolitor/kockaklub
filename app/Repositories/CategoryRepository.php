<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function findBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->first();
    }

    /**
     * Root categories with their `children` relation populated recursively,
     * restricted to active categories, ordered by name.
     *
     * @return Collection<int, Category>
     */
    public function activeTree(): Collection
    {
        return Category::tree(Category::query()->where('is_active', true)->orderBy('name')->get());
    }
}
