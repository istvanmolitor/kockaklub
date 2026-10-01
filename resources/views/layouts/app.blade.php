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
            <a href="{{ route('home') }}" class="text-xl font-semibold text-amber-600">Kockaklub</a>
            <nav class="flex items-center gap-6 text-sm">
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
