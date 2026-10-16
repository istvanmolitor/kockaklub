<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kockaklub')</title>
    @include('partials.seo-meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white text-gray-900 antialiased">
    <header class="sticky top-0 z-50">
        <div class="h-1.5 bg-gradient-to-r from-accent-500 via-accent2-500 to-accent3-400"></div>

        <div class="border-b border-gray-100 bg-white/90 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="flex h-20 items-center justify-center sm:h-24">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Kockaklub"
                             class="h-12 w-auto sm:h-16">
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
            @yield('content')
        </div>
    </main>

    @include('partials.storefront-footer')
</body>
</html>
