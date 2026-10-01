@extends('layouts.app')

@section('title', 'Termékek')

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
