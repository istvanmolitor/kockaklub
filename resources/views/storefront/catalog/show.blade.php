@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="mb-6 text-sm font-medium text-gray-500">
        <a href="{{ route('catalog.index') }}" class="hover:text-accent-600">Termékek</a>
        @foreach ($product->category->ancestors() as $ancestor)
            <span class="mx-2">/</span>
            <a href="{{ route('catalog.index', ['category' => $ancestor->slug]) }}" class="hover:text-accent-600">
                {{ $ancestor->name }}
            </a>
        @endforeach
        <span class="mx-2">/</span>
        <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" class="hover:text-accent-600">
            {{ $product->category->name }}
        </a>
    </nav>

    <div class="grid gap-8 md:grid-cols-2 md:gap-10">
        <div class="animate-rise">
            <div class="aspect-square overflow-hidden rounded-3xl border-2 border-gray-100 bg-white">
                <img src="{{ $product->default_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            </div>

            @if ($product->images->count() > 1)
                <div class="mt-4 grid grid-cols-4 gap-3">
                    @foreach ($product->images as $image)
                        <div class="aspect-square overflow-hidden rounded-2xl border-2 border-gray-100 bg-white">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="animate-rise">
            <h1 class="text-2xl font-black text-gray-900">{{ $product->name }}</h1>
            @if ($product->sku)
                <p class="mt-1 text-sm font-medium text-gray-500">Cikkszám: {{ $product->sku }}</p>
            @endif
            <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>

            <p class="mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold {{ $publicStock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                {{ $publicStock > 0 ? "Raktáron ({$publicStock} db)" : 'Elfogyott' }}
            </p>

            <div class="mt-6 prose prose-sm max-w-none text-gray-700">
                {!! $product->description !!}
            </div>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-8 flex items-end gap-4">
                @csrf
                <div>
                    <label for="quantity" class="field-label">Mennyiség</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" @if ($publicStock > 0) max="{{ $publicStock }}" @endif
                           class="input-field w-24">
                </div>
                <button type="submit"
                        @disabled(! $product->isOrderable($publicStock))
                        class="btn-primary">
                    {{ $product->isOrderable($publicStock) ? ($publicStock < 1 ? 'Előrendelem' : 'Kosárba teszem') : 'Elfogyott' }}
                </button>
            </form>
        </div>
    </div>

    @if ($product->relatedProducts->isNotEmpty())
        <div class="animate-rise mt-14">
            <h2 class="mb-5 text-2xl font-black text-gray-900">Kapcsolódó termékek</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
                @foreach ($product->relatedProducts as $related)
                    <x-storefront.product-card :product="$related" />
                @endforeach
            </div>
        </div>
    @endif
@endsection
