<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kockaklub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white text-gray-900 antialiased">
    <header x-data="{ mobileOpen: false, cartOpen: false, accountOpen: false, scrolled: false }"
            x-init="scrolled = window.scrollY > 8; window.addEventListener('scroll', () => scrolled = window.scrollY > 8)"
            @keydown.escape.window="mobileOpen = false; cartOpen = false; accountOpen = false"
            class="sticky top-0 z-50">
        <div class="h-1.5 bg-gradient-to-r from-accent-500 via-accent2-500 to-accent3-400"></div>

        <div :class="scrolled ? 'shadow-lg shadow-gray-900/5' : 'shadow-none'"
             class="border-b border-gray-100 bg-white/90 backdrop-blur-md transition-shadow duration-300">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="flex h-20 items-center justify-between gap-3 sm:h-24 sm:gap-6">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Kockaklub"
                             class="h-12 w-auto sm:h-16">
                    </a>

                    <form method="GET" action="{{ route('search.index') }}" class="relative hidden flex-1 max-w-md lg:block">
                        <input type="text" name="q" value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                               placeholder="Mit keresel?" autocomplete="off"
                               class="w-full rounded-full border-2 border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium transition duration-200 focus:border-accent-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-accent-500/15">
                        <button type="submit" aria-label="Keresés" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-accent-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z" />
                            </svg>
                        </button>
                    </form>

                    <nav class="hidden items-center gap-1 text-sm font-bold md:flex">
                        <div class="group relative">
                            <button type="button" class="flex items-center gap-1 rounded-full px-4 py-2.5 text-gray-700 transition duration-200 hover:bg-accent-50 hover:text-accent-700">
                                Termékek
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:-rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="invisible absolute left-1/2 top-full z-40 w-screen max-w-3xl -translate-x-1/2 translate-y-2 opacity-0 transition-all duration-200 ease-out group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                                <div class="mx-4 overflow-hidden rounded-3xl border-2 border-gray-100 bg-white shadow-2xl shadow-gray-900/10">
                                    <div class="grid gap-8 p-8 {{ $headerCategories->isNotEmpty() ? 'sm:grid-cols-[2fr_1fr]' : '' }}">
                                        @if ($headerCategories->isNotEmpty())
                                            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">
                                                @foreach ($headerCategories->take(6) as $category)
                                                    <div>
                                                        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                                           class="block font-bold text-gray-900 transition hover:text-accent-600">
                                                            {{ $category->name }}
                                                        </a>
                                                        @if ($category->children->isNotEmpty())
                                                            <ul class="mt-2 space-y-1.5">
                                                                @foreach ($category->children->take(5) as $child)
                                                                    <li>
                                                                        <a href="{{ route('catalog.index', ['category' => $child->slug]) }}"
                                                                           class="text-sm font-medium text-gray-500 transition hover:text-accent-600">
                                                                            {{ $child->name }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <a href="{{ route('catalog.index') }}"
                                           class="flex flex-col justify-between rounded-2xl bg-gradient-to-br from-accent-600 via-accent2-600 to-accent3-500 p-6 text-white transition duration-300 hover:scale-[1.02]">
                                            <span class="text-lg font-black leading-tight">Nézd meg az<br>összes termékünket</span>
                                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold">
                                                Irány a bolt
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('contact.create') }}" class="rounded-full px-4 py-2.5 text-gray-700 transition duration-200 hover:bg-accent-50 hover:text-accent-700">
                            Kapcsolat
                        </a>

                        @guest
                            <a href="{{ route('login') }}" class="rounded-full px-4 py-2.5 text-gray-700 transition duration-200 hover:bg-accent-50 hover:text-accent-700">
                                Bejelentkezés
                            </a>
                            <a href="{{ route('register') }}" class="btn-primary btn-sm ml-1">
                                Regisztráció
                            </a>
                        @endguest
                    </nav>

                    <div class="flex items-center gap-1.5 sm:gap-2">
                        @auth
                            <div class="relative">
                                <button type="button" @click="accountOpen = !accountOpen; cartOpen = false; mobileOpen = false" :aria-expanded="accountOpen.toString()"
                                        aria-label="Fiókom megnyitása" class="flex h-11 w-11 items-center justify-center rounded-full text-gray-600 transition duration-200 hover:bg-accent-50 hover:text-accent-700 sm:h-12 sm:w-12">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>

                                <div x-show="accountOpen" x-cloak @click.outside="accountOpen = false"
                                     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border-2 border-gray-100 bg-white shadow-2xl shadow-gray-900/10">
                                    <div class="py-2">
                                        <a href="{{ route('account.show') }}" class="block px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Profilom</a>
                                        <a href="{{ route('account.orders') }}" class="block px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Rendeléseim</a>
                                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                                            @csrf
                                            <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Kijelentkezés</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth

                        <div class="relative">
                            <button type="button" @click="cartOpen = !cartOpen; mobileOpen = false; accountOpen = false" :aria-expanded="cartOpen.toString()"
                                    aria-label="Kosár megnyitása" class="relative flex h-11 w-11 items-center justify-center rounded-full text-gray-600 transition duration-200 hover:bg-accent-50 hover:text-accent-700 sm:h-12 sm:w-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.344 1.087.836l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.693 2.609-7.164.067-.27-.148-.53-.426-.53H5.106M7.5 14.25L5.106 5.272M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                @if ($headerCartItems->sum('quantity') > 0)
                                    <span class="cart-badge absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-accent-600 to-accent3-500 px-1 text-xs font-bold text-white shadow-sm">
                                        {{ $headerCartItems->sum('quantity') }}
                                    </span>
                                @endif
                            </button>

                            <div x-show="cartOpen" x-cloak @click.outside="cartOpen = false"
                                 x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 z-50 mt-2 w-80 max-w-[90vw] overflow-hidden rounded-2xl border-2 border-gray-100 bg-white shadow-2xl shadow-gray-900/10 sm:w-96">
                                <div class="max-h-96 overflow-y-auto p-4">
                                    @if ($headerCartItems->isEmpty())
                                        <p class="py-6 text-center text-sm text-gray-500">A kosarad jelenleg üres.</p>
                                    @else
                                        <ul class="divide-y divide-gray-100">
                                            @foreach ($headerCartItems as $item)
                                                <li class="flex items-center gap-3 py-3">
                                                    <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="h-12 w-12 shrink-0 rounded-xl object-cover">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $item->quantity }} &times; {{ number_format($item->product->price, 0, ',', ' ') }} Ft</p>
                                                    </div>
                                                    <p class="shrink-0 text-sm font-bold text-gray-900">{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</p>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                @if ($headerCartItems->isNotEmpty())
                                    <div class="border-t border-gray-100 p-4">
                                        <div class="flex items-center justify-between text-sm font-bold text-gray-900">
                                            <span>Részösszeg</span>
                                            <span>{{ number_format($headerCartItems->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft</span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-2">
                                            <a href="{{ route('cart.show') }}" class="rounded-full border-2 border-gray-200 px-3 py-2 text-center text-sm font-bold text-gray-700 transition hover:border-accent-300 hover:text-accent-700">Kosár</a>
                                            <a href="{{ route('checkout.create') }}" class="rounded-full bg-gradient-to-r from-accent-600 to-accent3-500 px-3 py-2 text-center text-sm font-bold text-white shadow-sm transition hover:shadow-md">Pénztár</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <button type="button" @click="mobileOpen = !mobileOpen; cartOpen = false; accountOpen = false" :aria-expanded="mobileOpen.toString()"
                                aria-label="Menü megnyitása" class="flex h-11 w-11 items-center justify-center rounded-full text-gray-600 transition duration-200 hover:bg-accent-50 hover:text-accent-700 md:hidden">
                            <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                            <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak @click.outside="mobileOpen = false"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="max-h-[calc(100vh-5rem)] overflow-y-auto border-b-2 border-gray-100 bg-white shadow-xl md:hidden">
            <div class="mx-auto max-w-7xl space-y-5 px-4 py-5">
                <form method="GET" action="{{ route('search.index') }}" class="relative">
                    <input type="text" name="q" value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                           placeholder="Mit keresel?" autocomplete="off"
                           class="w-full rounded-full border-2 border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium focus:border-accent-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-accent-500/15">
                    <button type="submit" aria-label="Keresés" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z" />
                        </svg>
                    </button>
                </form>

                @if ($headerCategories->isNotEmpty())
                    <div class="rounded-2xl bg-gray-50 p-3">
                        <p class="px-2 pb-1 text-xs font-bold uppercase tracking-wide text-gray-400">Kategóriák</p>
                        <a href="{{ route('catalog.index') }}" class="block px-2 py-2 text-base font-bold text-accent-600">
                            Összes termék
                        </a>
                        @foreach ($headerCategories as $category)
                            <x-storefront.mobile-category-branch :category="$category" />
                        @endforeach
                    </div>
                @endif

                <nav class="flex flex-col gap-1 text-base font-bold">
                    <a href="{{ route('contact.create') }}" class="rounded-xl px-2 py-2.5 text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Kapcsolat</a>
                    <a href="{{ route('cart.show') }}" class="rounded-xl px-2 py-2.5 text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Kosár</a>
                    @auth
                        <a href="{{ route('account.show') }}" class="rounded-xl px-2 py-2.5 text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Profilom</a>
                        <a href="{{ route('account.orders') }}" class="rounded-xl px-2 py-2.5 text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Rendeléseim</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-2 py-2.5 text-left text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Kijelentkezés</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl px-2 py-2.5 text-gray-700 transition hover:bg-accent-50 hover:text-accent-700">Bejelentkezés</a>
                        <a href="{{ route('register') }}" class="btn-primary mt-1 justify-center">Regisztráció</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
            @if (session('status'))
                <div class="animate-rise mb-6 rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="animate-rise mb-6 rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-3.5 text-sm font-semibold text-rose-800">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="relative overflow-hidden border-t-2 border-gray-100 bg-gradient-to-br from-accent-50 via-white to-accent3-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:py-12">
            <div class="grid gap-8 sm:grid-cols-2">
                <div>
                    <p class="text-lg font-black text-gray-900">{{ setting('company_name', 'Kockaklub') }}</p>
                    <div class="mt-3 space-y-1.5 text-sm font-medium text-gray-500">
                        @if (setting('contact_address'))
                            <p>{{ setting('contact_address') }}</p>
                        @endif
                        @if (setting('contact_phone'))
                            <p><a href="tel:{{ setting('contact_phone') }}" class="transition hover:text-accent-600">{{ setting('contact_phone') }}</a></p>
                        @endif
                        @if (setting('contact_email'))
                            <p><a href="mailto:{{ setting('contact_email') }}" class="transition hover:text-accent-600">{{ setting('contact_email') }}</a></p>
                        @endif
                    </div>
                </div>

                @if (setting('facebook_url') || setting('instagram_url') || setting('youtube_url'))
                    <div class="sm:text-right">
                        <p class="text-lg font-black text-gray-900">Kövess minket</p>
                        <div class="mt-3 flex gap-3 sm:justify-end">
                            @if (setting('facebook_url'))
                                <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" aria-label="Facebook"
                                   class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.775-1.63 1.57v1.88h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/>
                                    </svg>
                                </a>
                            @endif
                            @if (setting('instagram_url'))
                                <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener" aria-label="Instagram"
                                   class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <rect x="3" y="3" width="18" height="18" rx="5" />
                                        <circle cx="12" cy="12" r="4" />
                                        <circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" />
                                    </svg>
                                </a>
                            @endif
                            @if (setting('youtube_url'))
                                <a href="{{ setting('youtube_url') }}" target="_blank" rel="noopener" aria-label="Youtube"
                                   class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a2.994 2.994 0 00-2.108-2.12C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.39.566a2.994 2.994 0 00-2.108 2.12A31.33 31.33 0 000 12a31.33 31.33 0 00.502 5.814 2.994 2.994 0 002.108 2.12C4.495 20.5 12 20.5 12 20.5s7.505 0 9.39-.566a2.994 2.994 0 002.108-2.12A31.33 31.33 0 0024 12a31.33 31.33 0 00-.502-5.814zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 border-t border-accent-100 pt-6 text-sm font-medium text-gray-500">
                &copy; {{ now()->year }} {{ setting('company_name', 'Kockaklub') }}
            </div>
        </div>
    </footer>
</body>
</html>
