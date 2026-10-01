@extends('layouts.app')

@section('title', 'Kockaklub')

@section('content')
    <div class="relative mb-14 overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-accent-600 via-accent2-600 to-accent3-500 px-6 py-14 text-center text-white sm:py-20">
        <div class="blob absolute -left-16 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="blob absolute -right-10 bottom-0 h-72 w-72 rounded-full bg-accent3-300/20" style="animation-delay: -5s"></div>
        <div class="blob absolute left-1/3 top-1/2 h-40 w-40 rounded-full bg-accent-300/15" style="animation-delay: -9s"></div>

        <div class="animate-rise relative">
            <h1 class="text-3xl font-black tracking-tight sm:text-5xl">Üdvözlünk a Kockaklubban</h1>
            <p class="mt-3 text-base font-medium text-white/90 sm:text-lg">Fedezd fel legújabb termékeinket.</p>
            <a href="{{ route('catalog.index') }}"
               class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-bold text-accent-700 shadow-xl shadow-accent-900/20 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-2xl active:translate-y-0 active:scale-95">
                Termékek böngészése
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </div>

    @if ($recommendedProducts->isNotEmpty())
        <div class="animate-rise mb-14">
            <h2 class="mb-5 text-2xl font-black text-gray-900">Neked ajánljuk</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
                @foreach ($recommendedProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif

    @if (setting('facebook_url') || setting('instagram_url') || setting('youtube_url'))
        <div class="animate-rise mb-14">
            <h2 class="mb-5 text-center text-2xl font-black text-gray-900">Kövess minket</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                @if (setting('facebook_url'))
                    <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener"
                       class="card card-hover flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.775-1.63 1.57v1.88h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-lg font-bold text-gray-900">Facebook</span>
                            <span class="block text-sm font-medium text-gray-500">Kövess minket Facebookon</span>
                        </span>
                    </a>
                @endif

                @if (setting('instagram_url'))
                    <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener"
                       class="card card-hover flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-pink-50 text-pink-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <circle cx="12" cy="12" r="4" />
                                <circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-lg font-bold text-gray-900">Instagram</span>
                            <span class="block text-sm font-medium text-gray-500">Kövess minket Instagramon</span>
                        </span>
                    </a>
                @endif

                @if (setting('youtube_url'))
                    <a href="{{ setting('youtube_url') }}" target="_blank" rel="noopener"
                       class="card card-hover flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a2.994 2.994 0 00-2.108-2.12C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.39.566a2.994 2.994 0 00-2.108 2.12A31.33 31.33 0 000 12a31.33 31.33 0 00.502 5.814 2.994 2.994 0 002.108 2.12C4.495 20.5 12 20.5 12 20.5s7.505 0 9.39-.566a2.994 2.994 0 002.108-2.12A31.33 31.33 0 0024 12a31.33 31.33 0 00-.502-5.814zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-lg font-bold text-gray-900">YouTube</span>
                            <span class="block text-sm font-medium text-gray-500">Nézd meg csatornánkat</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ($featuredProducts->isNotEmpty())
        <div class="animate-rise mb-14">
            <h2 class="mb-5 text-2xl font-black text-gray-900">Kiemelt termékek</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="animate-rise mb-14">
        <h2 class="mb-5 text-2xl font-black text-gray-900">Újdonságok</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
            @foreach ($newProducts as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    </div>
@endsection
