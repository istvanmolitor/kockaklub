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

    @php
        $galleryImages = $product->images->isNotEmpty()
            ? $product->images->map(fn ($image) => ['url' => $image->url(), 'alt' => $image->alt_text ?? $product->name])->values()->all()
            : [['url' => $product->default_image_url, 'alt' => $product->name]];
    @endphp

    <div class="grid gap-8 md:grid-cols-2 md:gap-10">
        <div class="animate-rise" x-data="{
                images: @js($galleryImages),
                activeIndex: 0,
                lightboxOpen: false,
                next() { this.activeIndex = (this.activeIndex + 1) % this.images.length },
                prev() { this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length },
            }">
            <div class="aspect-square cursor-zoom-in overflow-hidden rounded-3xl border-2 border-gray-100 bg-white" @click="lightboxOpen = true">
                <img :src="images[activeIndex].url" :alt="images[activeIndex].alt" class="h-full w-full object-cover">
            </div>

            @if (count($galleryImages) > 1)
                <div class="mt-4 grid grid-cols-4 gap-3">
                    <template x-for="(image, index) in images" :key="index">
                        <button type="button" @click="activeIndex = index"
                                class="aspect-square overflow-hidden rounded-2xl border-2 bg-white transition"
                                :class="activeIndex === index ? 'border-accent-500' : 'border-gray-100'">
                            <img :src="image.url" :alt="image.alt" class="h-full w-full object-cover">
                        </button>
                    </template>
                </div>
            @endif

            <template x-teleport="body">
                <div x-show="lightboxOpen" x-cloak
                     @keydown.escape.window="lightboxOpen = false"
                     @keydown.arrow-right.window="next()"
                     @keydown.arrow-left.window="prev()"
                     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/90 p-4"
                     @click.self="lightboxOpen = false">

                    <button type="button" @click="lightboxOpen = false"
                            class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <template x-if="images.length > 1">
                        <button type="button" @click.stop="prev()"
                                class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </template>

                    <img :src="images[activeIndex].url" :alt="images[activeIndex].alt" class="max-h-[85vh] max-w-full rounded-2xl object-contain">

                    <template x-if="images.length > 1">
                        <button type="button" @click.stop="next()"
                                class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </template>

                    <template x-if="images.length > 1">
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-sm font-bold text-white/80" x-text="(activeIndex + 1) + ' / ' + images.length"></div>
                    </template>
                </div>
            </template>
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
