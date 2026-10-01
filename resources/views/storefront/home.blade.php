@extends('layouts.app')

@section('title', 'Kockaklub')

@section('content')
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Üdvözlünk a Kockaklubban</h1>
        <p class="mt-2 text-gray-600">Fedezd fel legújabb termékeinket.</p>
        <a href="{{ route('catalog.index') }}" class="inline-block mt-4 rounded-md bg-amber-600 px-5 py-2.5 text-white font-medium hover:bg-amber-700">
            Termékek böngészése
        </a>
    </div>

    @if ($recommendedProducts->isNotEmpty())
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Neked ajánljuk</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($recommendedProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif

    @if (setting('facebook_url') || setting('instagram_url') || setting('youtube_url'))
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Kövess minket</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @if (setting('facebook_url'))
                    <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener"
                       class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.775-1.63 1.57v1.88h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-900">Facebook</span>
                            <span class="block text-sm text-gray-500">Kövess minket Facebookon</span>
                        </span>
                    </a>
                @endif

                @if (setting('instagram_url'))
                    <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener"
                       class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-pink-50 text-pink-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-900">Instagram</span>
                            <span class="block text-sm text-gray-500">Kövess minket Instagramon</span>
                        </span>
                    </a>
                @endif

                @if (setting('youtube_url'))
                    <a href="{{ setting('youtube_url') }}" target="_blank" rel="noopener"
                       class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a2.994 2.994 0 00-2.108-2.12C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.39.566a2.994 2.994 0 00-2.108 2.12A31.33 31.33 0 000 12a31.33 31.33 0 00.502 5.814 2.994 2.994 0 002.108 2.12C4.495 20.5 12 20.5 12 20.5s7.505 0 9.39-.566a2.994 2.994 0 002.108-2.12A31.33 31.33 0 0024 12a31.33 31.33 0 00-.502-5.814zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-gray-900">YouTube</span>
                            <span class="block text-sm text-gray-500">Nézd meg csatornánkat</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ($featuredProducts->isNotEmpty())
        <div class="mb-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Kiemelt termékek</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($featuredProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-10">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Újdonságok</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($newProducts as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    </div>
@endsection
