@props(['product'])

<div class="group flex flex-col overflow-hidden rounded-3xl border-2 border-gray-100 bg-white transition-all duration-300 hover:-translate-y-1.5 hover:border-accent-200 hover:shadow-xl hover:shadow-accent-500/10">
    <a href="{{ route('catalog.show', $product) }}" class="block overflow-hidden">
        <img src="{{ $product->default_image_url }}" alt="{{ $product->name }}"
             class="aspect-square w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110">
    </a>
    <div class="flex flex-1 flex-col p-4">
        <a href="{{ route('catalog.show', $product) }}" class="font-bold text-gray-900 transition hover:text-accent-600">
            {{ $product->name }}
        </a>
        <p class="mt-1 text-lg font-black text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>

        @if ($product->isOrderable($product->public_stock))
            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-auto pt-4">
                @csrf
                <button type="submit"
                        class="w-full rounded-full bg-gradient-to-r from-accent-600 to-accent3-500 px-3 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-95">
                    Kosárba
                </button>
            </form>
        @else
            <div class="mt-auto pt-4">
                <a href="{{ route('catalog.show', $product) }}"
                   class="block w-full rounded-full bg-gray-200 px-3 py-2.5 text-center text-sm font-bold text-gray-600 transition-colors duration-200 hover:bg-gray-300">
                    Termék megtekintése
                </a>
            </div>
        @endif
    </div>
</div>
