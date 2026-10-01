@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('catalog.index') }}" class="hover:text-amber-600">Termékek</a>
        @foreach ($product->category->ancestors() as $ancestor)
            <span class="mx-2">/</span>
            <a href="{{ route('catalog.index', ['category' => $ancestor->slug]) }}" class="hover:text-amber-600">
                {{ $ancestor->name }}
            </a>
        @endforeach
        <span class="mx-2">/</span>
        <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" class="hover:text-amber-600">
            {{ $product->category->name }}
        </a>
    </nav>

    <div class="grid md:grid-cols-2 gap-10">
        <div>
            <div class="aspect-square bg-white border border-gray-200 rounded-lg overflow-hidden">
                <img src="{{ $product->default_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>

            @if ($product->images->count() > 1)
                <div class="mt-4 grid grid-cols-4 gap-3">
                    @foreach ($product->images as $image)
                        <div class="aspect-square bg-white border border-gray-200 rounded-md overflow-hidden">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>

            <p class="mt-2 text-sm {{ $publicStock > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $publicStock > 0 ? "Raktáron ({$publicStock} db)" : 'Elfogyott' }}
            </p>

            <div class="mt-6 prose prose-sm max-w-none text-gray-700">
                {!! $product->description !!}
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 flex items-end gap-4">
                @csrf
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Mennyiség</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" @if ($publicStock > 0) max="{{ $publicStock }}" @endif
                           class="mt-1 w-20 rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <button type="submit"
                        @disabled(! $product->isOrderable($publicStock))
                        class="rounded-md bg-amber-600 px-5 py-2.5 text-white font-medium hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                    {{ $product->isOrderable($publicStock) ? ($publicStock < 1 ? 'Előrendelem' : 'Kosárba teszem') : 'Elfogyott' }}
                </button>
            </form>
        </div>
    </div>

    @if ($product->relatedProducts->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Kapcsolódó termékek</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($product->relatedProducts as $related)
                    <x-storefront.product-card :product="$related" />
                @endforeach
            </div>
        </div>
    @endif
@endsection
