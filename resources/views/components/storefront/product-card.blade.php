@props(['product'])

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden flex flex-col">
    <a href="{{ route('catalog.show', $product) }}">
        <img src="{{ $product->default_image_url }}" alt="{{ $product->name }}" class="w-full aspect-square object-cover">
    </a>
    <div class="p-4 flex flex-col flex-1">
        <a href="{{ route('catalog.show', $product) }}" class="font-medium text-gray-900 hover:text-amber-600">
            {{ $product->name }}
        </a>
        <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>

        <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-auto pt-4">
            @csrf
            <button type="submit"
                    @disabled($product->public_stock < 1)
                    class="w-full rounded-md bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                {{ $product->public_stock < 1 ? 'Elfogyott' : 'Kosárba' }}
            </button>
        </form>
    </div>
</div>
