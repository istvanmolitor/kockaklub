@extends('layouts.app')

@php
    $categoryArticle = $category && preg_match('/^[aáeéiíoóöőuúüű]/iu', $category->name) ? 'az' : 'a';
    $metaDescription = $category
        ? (($category->description ? \Illuminate\Support\Str::of($category->description)->stripTags()->squish()->limit(160)->toString() : null)
            ?: "Fedezd fel {$categoryArticle} {$category->name} kategória termékeit a Kockaklubban.")
        : 'Nézd át a Kockaklub teljes termékkínálatát: társasjátékok, kártyajátékok és kiegészítők egy helyen.';

    $breadcrumbItems = collect([['name' => 'Főoldal', 'url' => route('home')]])
        ->when($category, fn ($items) => $items
            ->concat($category->ancestors()->map(fn ($ancestor) => [
                'name' => $ancestor->name,
                'url' => route('catalog.index', ['category' => $ancestor->slug]),
            ]))
            ->push(['name' => $category->name, 'url' => route('catalog.index', ['category' => $category->slug])]))
        ->when(! $category, fn ($items) => $items->push(['name' => 'Termékek', 'url' => route('catalog.index')]));
@endphp

@section('title', $category ? "{$category->name} | Kockaklub" : 'Termékek | Kockaklub')
@section('meta_description', $metaDescription)

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems->values()->map(fn ($item, $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $item['name'],
        'item' => $item['url'],
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    <div class="flex flex-col gap-8 sm:flex-row">
        <aside class="card sm:w-56 shrink-0 p-4">
            <h2 class="mb-3 px-2 text-sm font-black uppercase tracking-wide text-gray-400">Kategóriák</h2>
            <ul class="space-y-1.5 text-sm">
                <li>
                    <a href="{{ route('catalog.index') }}"
                       class="block rounded-xl px-2 py-1.5 font-semibold transition {{ $activeCategory === '' ? 'bg-accent-50 text-accent-700' : 'text-gray-600 hover:bg-accent-50 hover:text-accent-700' }}">
                        Összes termék
                    </a>
                </li>
            </ul>

            @include('storefront.catalog._category-tree', [
                'categories' => $categories,
                'activeCategory' => $activeCategory,
                'level' => 0,
            ])
        </aside>

        <div class="flex-1">
            @if ($products->isEmpty())
                <p class="font-medium text-gray-600">Nincs megjeleníthető termék.</p>
            @else
                <div class="flex items-center justify-end mb-6">
                    @include('storefront.partials.sort-select')
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
                    @foreach ($products as $product)
                        <x-storefront.product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
