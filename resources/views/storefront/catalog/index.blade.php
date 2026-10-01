@extends('layouts.app')

@section('title', 'Termékek')

@section('content')
    <div class="flex flex-col sm:flex-row gap-8">
        <aside class="sm:w-48 shrink-0">
            <h2 class="font-semibold text-gray-900 mb-3">Kategóriák</h2>
            <ul class="space-y-2 text-sm">
                <li>
                    <a href="{{ route('catalog.index') }}"
                       class="{{ $activeCategory === '' ? 'text-amber-600 font-medium' : 'text-gray-600 hover:text-amber-600' }}">
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
                <p class="text-gray-600">Nincs megjeleníthető termék.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
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
