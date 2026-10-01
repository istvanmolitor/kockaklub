@extends('layouts.app')

@section('title', 'Kosár')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Kosár</h1>

    @if ($cart->items->isEmpty())
        <p class="text-gray-600">A kosarad jelenleg üres.</p>
        <a href="{{ route('catalog.index') }}" class="inline-block mt-4 text-amber-600 hover:underline">Vissza a termékekhez</a>
    @else
        <div class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-200">
            @foreach ($cart->items as $item)
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="w-16 h-16 object-cover rounded-md">

                    <div class="flex-1">
                        <a href="{{ route('catalog.show', $item->product) }}" class="font-medium text-gray-900 hover:text-amber-600">
                            {{ $item->product->name }}
                        </a>
                        <p class="text-sm text-gray-500">{{ number_format($item->product->price, 0, ',', ' ') }} Ft / db</p>
                    </div>

                    <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}"
                               class="w-16 rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <button type="submit" class="text-sm text-amber-600 hover:underline">Frissít</button>
                    </form>

                    <p class="w-28 text-right font-medium">{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</p>

                    <form method="POST" action="{{ route('cart.destroy', $item->product) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Törlés</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <div class="text-right">
                <p class="text-lg font-semibold">
                    Összesen: {{ number_format($cart->items->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft
                </p>
                <a href="{{ route('checkout.create') }}"
                   class="inline-block mt-4 rounded-md bg-amber-600 px-5 py-2.5 text-white font-medium hover:bg-amber-700">
                    Tovább a pénztárhoz
                </a>
            </div>
        </div>
    @endif
@endsection
