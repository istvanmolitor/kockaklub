<ul class="space-y-1.5 text-sm {{ $level > 0 ? 'ml-3 mt-1.5 border-l-2 border-accent-100 pl-3' : '' }}">
    @foreach ($categories as $category)
        <li>
            <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
               class="block rounded-xl px-2 py-1.5 font-semibold transition {{ $activeCategory === $category->slug ? 'bg-accent-50 text-accent-700' : 'text-gray-600 hover:bg-accent-50 hover:text-accent-700' }}">
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
