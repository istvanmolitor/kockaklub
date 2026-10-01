<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kockaklub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Kockaklub" class="h-10 w-auto">
            </a>
            <nav class="flex items-center gap-6 text-sm">
                <form method="GET" action="{{ route('search.index') }}" class="relative">
                    <input type="text" name="q" value="{{ request()->routeIs('search.index') ? request('q') : '' }}"
                           placeholder="Mit keresel?" autocomplete="off"
                           class="w-40 sm:w-56 rounded-md border border-gray-300 py-1.5 pl-8 pr-3 text-sm focus:border-amber-500 focus:ring-amber-500">
                    <button type="submit" aria-label="Keresés" class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z" />
                        </svg>
                    </button>
                </form>

                <a href="{{ route('catalog.index') }}" class="hover:text-amber-600">Termékek</a>
                <a href="{{ route('cart.show') }}" class="hover:text-amber-600">Kosár</a>
                @auth
                    <a href="{{ route('account.show') }}" class="hover:text-amber-600">Fiókom</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-amber-600">Kijelentkezés</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-amber-600">Bejelentkezés</a>
                    <a href="{{ route('register') }}" class="hover:text-amber-600">Regisztráció</a>
                @endauth
            </nav>
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
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-gray-500">
            &copy; {{ now()->year }} Kockaklub
        </div>
    </footer>
</body>
</html>
