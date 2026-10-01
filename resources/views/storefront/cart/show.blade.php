@extends('layouts.app')

@section('title', 'Kosár')

@section('content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Kosár</h1>

    @if ($cart->items->isEmpty())
        <p class="font-medium text-gray-600">A kosarad jelenleg üres.</p>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-block font-bold text-accent-600 hover:underline">Vissza a termékekhez</a>
    @else
        <div class="card divide-y divide-gray-100 p-0">
            @foreach ($cart->items as $item)
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="h-16 w-16 rounded-2xl object-cover">

                    <div class="flex-1">
                        <a href="{{ route('catalog.show', $item->product) }}" class="font-bold text-gray-900 hover:text-accent-600">
                            {{ $item->product->name }}
                        </a>
                        <p class="text-sm font-medium text-gray-500">{{ number_format($item->product->price, 0, ',', ' ') }} Ft / db</p>
                    </div>

                    @php $itemStock = $publicStockByProductId[$item->product_id] ?? 0; @endphp
                    <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" @if ($itemStock > 0) max="{{ $itemStock }}" @endif
                               class="input-field mt-0 w-20 py-2">
                        <button type="submit" class="text-sm font-bold text-accent-600 hover:underline">Frissít</button>
                    </form>

                    <p class="w-28 text-right font-bold text-gray-900">{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</p>

                    <form method="POST" action="{{ route('cart.destroy', $item->product) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-bold text-rose-600 hover:underline">Törlés</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <div class="text-right">
                <p class="text-lg font-black text-gray-900">
                    Összesen: {{ number_format($cart->items->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft
                </p>
                <a href="{{ route('checkout.create') }}" class="btn-primary mt-4">
                    Tovább a pénztárhoz
                </a>
            </div>
        </div>
    @endif
@endsection
