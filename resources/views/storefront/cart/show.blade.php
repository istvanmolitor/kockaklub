@extends('layouts.app')

@section('title', 'Kosár')

@section('content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Kosár</h1>

    @if ($cart->items->isEmpty())
        <p class="font-medium text-gray-600">A kosarad jelenleg üres.</p>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-block font-bold text-accent-600 hover:underline">Vissza a termékekhez</a>
    @else
        @php
            $hasLowStockItems = $cart->items->contains(
                fn ($item) => $item->quantity > ($freeStockByProductId[$item->product_id] ?? 0)
            );
        @endphp

        @if ($hasLowStockItems)
            <div class="mb-4 rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-700">
                Egyes termékekből a kosárban szereplő mennyiség meghaladja a szabad készletet.
            </div>
        @endif

        <div class="card divide-y divide-gray-100 p-0">
            @foreach ($cart->items as $item)
                @php
                    $itemStock = $freeStockByProductId[$item->product_id] ?? 0;
                    $isLowStock = $item->quantity > $itemStock;
                @endphp
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="h-16 w-16 rounded-2xl object-cover">

                    <div class="flex-1">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('catalog.show', $item->product) }}" class="font-bold text-gray-900 hover:text-accent-600">
                                {{ $item->product->name }}
                            </a>

                            @if ($isLowStock)
                                <span class="group relative inline-flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.28 11.167c.75 1.333-.213 2.984-1.743 2.984H3.72c-1.53 0-2.493-1.651-1.743-2.984L8.257 3.1zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 w-56 -translate-x-1/2 rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white opacity-0 transition group-hover:opacity-100">
                                        A megadott mennyiség nincs szabad készleten.
                                    </span>
                                </span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-500">{{ number_format($item->product->price, 0, ',', ' ') }} Ft / db</p>
                    </div>

                    <form method="POST" action="{{ route('cart.update', $item->product) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" @if ($item->product->is_discontinued && $itemStock > 0) max="{{ $itemStock }}" @endif
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
                <a href="{{ route('checkout.gate') }}" class="btn-primary mt-4">
                    Tovább a pénztárhoz
                </a>
            </div>
        </div>
    @endif
@endsection
