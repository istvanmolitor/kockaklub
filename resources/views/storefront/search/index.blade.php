@extends('layouts.app')

@section('title', $query !== '' ? "Keresés – {$query}" : 'Keresés')

@section('content')
    <form method="GET" action="{{ route('search.index') }}" class="mb-8 flex max-w-md gap-2">
        <input type="text" name="q" value="{{ $query }}" placeholder="Mit keresel?" autocomplete="off"
               class="input-field mt-0 flex-1">
        <button type="submit" class="btn-primary">
            Keresés
        </button>
    </form>

    @if ($query === '')
        <p class="font-medium text-gray-600">Adj meg egy keresési kifejezést.</p>
    @elseif ($products->isEmpty())
        <p class="font-medium text-gray-600">Nincs a keresésnek megfelelő termék: &bdquo;{{ $query }}&rdquo;</p>
    @else
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-600">{{ $products->total() }} találat &bdquo;{{ $query }}&rdquo; keresésre</p>
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
@endsection
