@props(['code', 'title', 'message'])

<div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-accent-600 via-accent2-600 to-accent3-500 px-6 py-16 text-center text-white sm:py-24">
    <div class="blob absolute -left-16 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
    <div class="blob absolute -right-10 bottom-0 h-72 w-72 rounded-full bg-accent3-300/20" style="animation-delay: -5s"></div>
    <div class="blob absolute left-1/3 top-1/2 h-40 w-40 rounded-full bg-accent-300/15" style="animation-delay: -9s"></div>

    <div class="animate-rise relative mx-auto max-w-xl">
        <p class="text-7xl font-black tracking-tight sm:text-8xl">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl">{{ $title }}</h1>
        <p class="mt-3 text-base font-medium text-white/90 sm:text-lg">{{ $message }}</p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 rounded-full border-2 border-white/30 bg-white/10 px-6 py-3.5 text-base font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-white/50 hover:bg-white/20">
                Vissza a főoldalra
            </a>
            <a href="{{ route('catalog.index') }}"
               class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-base font-bold text-accent-700 shadow-xl shadow-accent-900/20 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-2xl active:translate-y-0 active:scale-95">
                Termékek böngészése
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </div>
</div>
