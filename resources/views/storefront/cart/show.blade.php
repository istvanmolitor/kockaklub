@extends('layouts.app')

@section('title', 'Kosár')
@section('robots', 'noindex,nofollow')

@section('content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Kosár</h1>

    @if ($cart->items->isEmpty())
        <div class="card flex flex-col items-center gap-3 py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.705 2.602-7.189.138-.563-.3-1.11-.878-1.11H5.106M7.5 14.25 5.106 5.25M7.5 14.25 5.106 5.25M8.25 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm9 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
            <p class="font-medium text-gray-600">A kosarad jelenleg üres.</p>
            <a href="{{ route('catalog.index') }}" class="btn-primary btn-sm mt-2">Vissza a termékekhez</a>
        </div>
    @else
        @php
            $hasLowStockItems = $cart->items->contains(
                fn ($item) => $item->quantity > ($freeStockByProductId[$item->product_id] ?? 0)
            );
        @endphp

        <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
            <div class="lg:col-span-2">
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
                            $hasMax = $item->product->is_discontinued && $itemStock > 0;
                        @endphp
                        <div class="flex flex-wrap items-center gap-4 p-5 transition hover:bg-gray-50/80">
                            <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="h-20 w-20 rounded-2xl object-cover shadow-sm">

                            <div class="min-w-[10rem] flex-1">
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

                            <form method="POST" action="{{ route('cart.update', $item->product) }}"
                                  x-data="{ qty: {{ $item->quantity }} }" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')

                                <div class="flex items-center overflow-hidden rounded-2xl border-2 border-gray-200 bg-white">
                                    <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1"
                                            class="flex h-10 w-9 items-center justify-center text-gray-500 transition hover:bg-gray-100 hover:text-accent-600 disabled:cursor-not-allowed disabled:opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </button>

                                    <input type="number" name="quantity" x-model.number="qty" min="1" @if ($hasMax) max="{{ $itemStock }}" @endif
                                           class="h-10 w-12 border-0 bg-transparent text-center text-sm font-bold text-gray-900 [appearance:textfield] focus:outline-none focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

                                    <button type="button" @click="qty = qty + 1" @if ($hasMax) :disabled="qty >= {{ $itemStock }}" @endif
                                            class="flex h-10 w-9 items-center justify-center text-gray-500 transition hover:bg-gray-100 hover:text-accent-600 disabled:cursor-not-allowed disabled:opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>

                                <button type="submit" title="Mennyiség frissítése"
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-gray-200 text-accent-600 transition hover:border-accent-300 hover:bg-accent-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </button>
                            </form>

                            <p class="w-28 text-right font-bold text-gray-900">{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</p>

                            <form method="POST" action="{{ route('cart.destroy', $item->product) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Törlés a kosárból"
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-gray-200 text-rose-600 transition hover:border-rose-300 hover:bg-rose-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="animate-pop relative overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm lg:sticky lg:top-24">
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

                <h2 class="relative text-lg font-black text-gray-900">Összesítő</h2>

                <div class="relative mt-5 flex justify-between text-sm font-medium text-gray-600">
                    <span>Tételek ({{ $cart->items->sum('quantity') }} db)</span>
                    <span class="font-bold text-gray-900">{{ number_format($cart->items->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft</span>
                </div>

                <div class="relative mt-4 border-t-2 border-gray-100 pt-4">
                    <div class="flex items-baseline justify-between">
                        <span class="font-bold text-gray-700">Összesen</span>
                        <span class="text-2xl font-black text-gray-900">{{ number_format($cart->items->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft</span>
                    </div>
                </div>

                <a href="{{ route('checkout.gate') }}" class="btn-primary relative mt-6 w-full justify-center">
                    Tovább a pénztárhoz
                </a>
            </div>
        </div>
    @endif
@endsection
