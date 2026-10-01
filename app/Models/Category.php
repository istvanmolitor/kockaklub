<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'parent_id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * This category's own id plus every descendant's id, used to include
     * subcategory products when filtering the storefront by a parent category.
     */
    public function selfAndDescendantIds(): array
    {
        return $this->children->reduce(
            fn (array $ids, Category $child) => [...$ids, ...$child->selfAndDescendantIds()],
            [$this->id],
        );
    }

    public function depth(): int
    {
        $depth = 0;

        for ($parent = $this->parent; $parent; $parent = $parent->parent) {
            $depth++;
        }

        return $depth;
    }

    /**
     * This category's ancestors, ordered from the root down to the immediate parent.
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();

        for ($parent = $this->parent; $parent; $parent = $parent->parent) {
            $ancestors->prepend($parent);
        }

        return $ancestors;
    }

    /**
     * Build a nested tree (root categories with their `children` relation populated
     * recursively) from a flat collection, without N+1 queries.
     */
    public static function tree(?Collection $categories = null): Collection
    {
        $categories ??= static::query()->get();

        $byParent = $categories->groupBy(fn (Category $category) => $category->parent_id ?? 0);

        $build = function (int $parentId) use (&$build, $byParent): Collection {
            return $byParent->get($parentId, collect())
                ->values()
                ->each(fn (Category $category) => $category->setRelation('children', $build($category->id)));
        };

        return $build(0);
    }
}
