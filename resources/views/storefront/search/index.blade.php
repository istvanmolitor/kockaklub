@extends('layouts.app')

@section('title', $query !== '' ? "Keresés – {$query}" : 'Keresés')

@section('content')
    <form method="GET" action="{{ route('search.index') }}" class="mb-8 max-w-md flex gap-2">
        <input type="text" name="q" value="{{ $query }}" placeholder="Mit keresel?" autocomplete="off"
               class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500">
        <button type="submit" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
            Keresés
        </button>
    </form>

    @if ($query === '')
        <p class="text-gray-600">Adj meg egy keresési kifejezést.</p>
    @elseif ($products->isEmpty())
        <p class="text-gray-600">Nincs a keresésnek megfelelő termék: &bdquo;{{ $query }}&rdquo;</p>
    @else
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-600">{{ $products->total() }} találat &bdquo;{{ $query }}&rdquo; keresésre</p>
            @include('storefront.partials.sort-select')
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
@endsection
