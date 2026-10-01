<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kockaklub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 flex flex-col">
    <header x-data="{ mobileOpen: false, cartOpen: false, accountOpen: false }" @keydown.escape.window="mobileOpen = false; cartOpen = false; accountOpen = false" class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex h-16 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Kockaklub" class="h-9 w-auto">
                </a>

                <form method="GET" action="{{ route('search.index') }}" class="relative hidden flex-1 max-w-sm sm:block">
                    <input type="text" name="q" value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                           placeholder="Mit keresel?" autocomplete="off"
                           class="w-full rounded-full border border-gray-300 bg-gray-50 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:bg-white focus:ring-amber-500">
                    <button type="submit" aria-label="Keresés" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z" />
                        </svg>
                    </button>
                </form>

                <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                    <a href="{{ route('catalog.index') }}" class="text-gray-700 hover:text-amber-600">Termékek</a>
                    <a href="{{ route('contact.create') }}" class="text-gray-700 hover:text-amber-600">Kapcsolat</a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-amber-600">Bejelentkezés</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-amber-600 px-4 py-2 text-white hover:bg-amber-700">Regisztráció</a>
                    @endguest
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <div class="relative">
                            <button type="button" @click="accountOpen = !accountOpen; cartOpen = false; mobileOpen = false" :aria-expanded="accountOpen.toString()"
                                    aria-label="Fiókom megnyitása" class="flex h-10 w-10 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>

                            <div x-show="accountOpen" x-cloak @click.outside="accountOpen = false" x-transition
                                 class="absolute right-0 z-50 mt-2 w-56 rounded-lg border border-gray-200 bg-white shadow-lg">
                                <div class="py-2">
                                    <a href="{{ route('account.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profilom</a>
                                    <a href="{{ route('account.orders') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Rendeléseim</a>
                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Kijelentkezés</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth

                    <div class="relative">
                        <button type="button" @click="cartOpen = !cartOpen; mobileOpen = false; accountOpen = false" :aria-expanded="cartOpen.toString()"
                                aria-label="Kosár megnyitása" class="relative flex h-10 w-10 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.344 1.087.836l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.693 2.609-7.164.067-.27-.148-.53-.426-.53H5.106M7.5 14.25L5.106 5.272M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            @if ($headerCartItems->sum('quantity') > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-600 px-1 text-xs font-semibold text-white">
                                    {{ $headerCartItems->sum('quantity') }}
                                </span>
                            @endif
                        </button>

                        <div x-show="cartOpen" x-cloak @click.outside="cartOpen = false" x-transition
                             class="absolute right-0 z-50 mt-2 w-80 max-w-[90vw] rounded-lg border border-gray-200 bg-white shadow-lg sm:w-96">
                            <div class="max-h-96 overflow-y-auto p-4">
                                @if ($headerCartItems->isEmpty())
                                    <p class="py-6 text-center text-sm text-gray-500">A kosarad jelenleg üres.</p>
                                @else
                                    <ul class="divide-y divide-gray-100">
                                        @foreach ($headerCartItems as $item)
                                            <li class="flex items-center gap-3 py-3">
                                                <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product->name }}" class="h-12 w-12 shrink-0 rounded-md object-cover">
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-gray-900">{{ $item->product->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->quantity }} &times; {{ number_format($item->product->price, 0, ',', ' ') }} Ft</p>
                                                </div>
                                                <p class="shrink-0 text-sm font-semibold text-gray-900">{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            @if ($headerCartItems->isNotEmpty())
                                <div class="border-t border-gray-200 p-4">
                                    <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
                                        <span>Részösszeg</span>
                                        <span>{{ number_format($headerCartItems->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <a href="{{ route('cart.show') }}" class="rounded-md border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">Kosár</a>
                                        <a href="{{ route('checkout.create') }}" class="rounded-md bg-amber-600 px-3 py-2 text-center text-sm font-medium text-white hover:bg-amber-700">Pénztár</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="button" @click="mobileOpen = !mobileOpen; cartOpen = false; accountOpen = false" :aria-expanded="mobileOpen.toString()"
                            aria-label="Menü megnyitása" class="flex h-10 w-10 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 md:hidden">
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

        <div x-show="mobileOpen" x-cloak x-transition @click.outside="mobileOpen = false" class="border-t border-gray-200 bg-white md:hidden">
            <div class="mx-auto max-w-6xl space-y-4 px-4 py-4">
                <form method="GET" action="{{ route('search.index') }}" class="relative">
                    <input type="text" name="q" value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                           placeholder="Mit keresel?" autocomplete="off"
                           class="w-full rounded-full border border-gray-300 bg-gray-50 py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:bg-white focus:ring-amber-500">
                    <button type="submit" aria-label="Keresés" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z" />
                        </svg>
                    </button>
                </form>

                <nav class="flex flex-col gap-3 text-sm font-medium">
                    <a href="{{ route('catalog.index') }}" class="text-gray-700 hover:text-amber-600">Termékek</a>
                    <a href="{{ route('contact.create') }}" class="text-gray-700 hover:text-amber-600">Kapcsolat</a>
                    <a href="{{ route('cart.show') }}" class="text-gray-700 hover:text-amber-600">Kosár</a>
                    @auth
                        <a href="{{ route('account.show') }}" class="text-gray-700 hover:text-amber-600">Profilom</a>
                        <a href="{{ route('account.orders') }}" class="text-gray-700 hover:text-amber-600">Rendeléseim</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-amber-600">Kijelentkezés</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-amber-600">Bejelentkezés</a>
                        <a href="{{ route('register') }}" class="text-gray-700 hover:text-amber-600">Regisztráció</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200">
        <div class="mx-auto max-w-6xl px-4 py-8">
            <div class="grid gap-8 sm:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ setting('company_name', 'Kockaklub') }}</p>
                    <div class="mt-2 space-y-1 text-sm text-gray-500">
                        @if (setting('contact_address'))
                            <p>{{ setting('contact_address') }}</p>
                        @endif
                        @if (setting('contact_phone'))
                            <p><a href="tel:{{ setting('contact_phone') }}" class="hover:text-amber-600">{{ setting('contact_phone') }}</a></p>
                        @endif
                        @if (setting('contact_email'))
                            <p><a href="mailto:{{ setting('contact_email') }}" class="hover:text-amber-600">{{ setting('contact_email') }}</a></p>
                        @endif
                    </div>
                </div>

                @if (setting('facebook_url') || setting('instagram_url') || setting('youtube_url'))
                    <div class="sm:text-right">
                        <p class="text-sm font-semibold text-gray-900">Kövess minket</p>
                        <div class="mt-2 flex gap-3 sm:justify-end">
                            @if (setting('facebook_url'))
                                <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" aria-label="Facebook"
                                   class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.775-1.63 1.57v1.88h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/>
                                    </svg>
                                </a>
                            @endif
                            @if (setting('instagram_url'))
                                <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener" aria-label="Instagram"
                                   class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <rect x="3" y="3" width="18" height="18" rx="5" />
                                        <circle cx="12" cy="12" r="4" />
                                        <circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" />
                                    </svg>
                                </a>
                            @endif
                            @if (setting('youtube_url'))
                                <a href="{{ setting('youtube_url') }}" target="_blank" rel="noopener" aria-label="Youtube"
                                   class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a2.994 2.994 0 00-2.108-2.12C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.39.566a2.994 2.994 0 00-2.108 2.12A31.33 31.33 0 000 12a31.33 31.33 0 00.502 5.814 2.994 2.994 0 002.108 2.12C4.495 20.5 12 20.5 12 20.5s7.505 0 9.39-.566a2.994 2.994 0 002.108-2.12A31.33 31.33 0 0024 12a31.33 31.33 0 00-.502-5.814zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6 text-sm text-gray-500">
                &copy; {{ now()->year }} {{ setting('company_name', 'Kockaklub') }}
            </div>
        </div>
    </footer>
</body>
</html>
