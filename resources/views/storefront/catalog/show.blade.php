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
            <h1 class="flex items-center gap-2 text-2xl font-black text-gray-900">
                {{ $product->name }}

                @if (auth()->user()?->is_admin)
                    <a href="{{ route('filament.admin.resources.products.edit', ['record' => $product->id]) }}"
                       title="Termék szerkesztése" class="text-gray-400 hover:text-accent-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                    </a>
                @endif
            </h1>
            @if ($product->sku)
                <p class="mt-1 text-sm font-medium text-gray-500">Cikkszám: {{ $product->sku }}</p>
            @endif
            <p class="mt-2 text-3xl font-black text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <p class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold {{ $freeStock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                    {{ $freeStock > 0 ? "Raktáron ({$freeStock} db)" : 'Elfogyott' }}
                </p>

                @if ($product->is_discontinued)
                    <p class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700">
                        Kifutó termék
                    </p>
                @endif
            </div>

            <div class="mt-6 prose prose-sm max-w-none text-gray-700">
                {!! $product->description !!}
            </div>

            @php
                $specs = $product->attributeValues
                    ->map(fn ($attributeValue) => ['label' => $attributeValue->attribute->name, 'value' => $attributeValue->value])
                    ->values();

                if ($product->weight) {
                    $specs->push([
                        'label' => 'Súly',
                        'value' => rtrim(rtrim(number_format($product->weight, 3, ',', ' '), '0'), ',').' kg',
                    ]);
                }
            @endphp

            @if ($specs->isNotEmpty())
                <dl class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($specs as $spec)
                        <div class="rounded-xl bg-gray-50 px-3 py-2">
                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $spec['label'] }}</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $spec['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @php $hasMax = $product->is_discontinued && $freeStock > 0; @endphp
            <form method="POST" action="{{ route('cart.store', $product) }}"
                  x-data="{ qty: 1 }" class="mt-8 flex items-end gap-4">
                @csrf
                <div>
                    <label for="quantity" class="field-label">Mennyiség</label>
                    <div class="mt-1.5 flex items-center overflow-hidden rounded-2xl border-2 border-gray-200 bg-white">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1"
                                class="flex h-12 w-10 items-center justify-center text-gray-500 transition hover:bg-gray-100 hover:text-accent-600 disabled:cursor-not-allowed disabled:opacity-30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            </svg>
                        </button>

                        <input type="number" id="quantity" name="quantity" x-model.number="qty" min="1" @if ($hasMax) max="{{ $freeStock }}" @endif
                               class="h-12 w-14 border-0 bg-transparent text-center text-sm font-bold text-gray-900 [appearance:textfield] focus:outline-none focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

                        <button type="button" @click="qty = qty + 1" @if ($hasMax) :disabled="qty >= {{ $freeStock }}" @endif
                                class="flex h-12 w-10 items-center justify-center text-gray-500 transition hover:bg-gray-100 hover:text-accent-600 disabled:cursor-not-allowed disabled:opacity-30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit"
                        @disabled(! $product->isOrderable($freeStock))
                        class="btn-primary">
                    {{ $product->isOrderable($freeStock) ? ($freeStock < 1 ? 'Előrendelem' : 'Kosárba teszem') : 'Elfogyott' }}
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
