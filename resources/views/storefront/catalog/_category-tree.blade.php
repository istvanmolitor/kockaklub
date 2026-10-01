<ul class="space-y-2 text-sm {{ $level > 0 ? 'ml-4 mt-2' : '' }}">
    @foreach ($categories as $category)
        <li>
            <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
               class="{{ $activeCategory === $category->slug ? 'text-amber-600 font-medium' : 'text-gray-600 hover:text-amber-600' }}">
                {{ $category->name }}
            </a>
            @if ($category->children->isNotEmpty())
                @include('storefront.catalog._category-tree', [
                    'categories' => $category->children,
                    'activeCategory' => $activeCategory,
                    'level' => $level + 1,
                ])
            @endif
        </li>
    @endforeach
</ul>
