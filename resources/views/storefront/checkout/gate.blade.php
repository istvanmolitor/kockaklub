@extends('layouts.app')

@section('title', 'Tovább a pénztárhoz')

@section('content')
    <div class="animate-rise mx-auto max-w-5xl">
        <div class="text-center">
            <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">Hogyan szeretnél folytatni?</h1>
            <p class="mt-2 font-medium text-gray-500">Jelentkezz be, regisztrálj, vagy folytasd fiók nélkül &ndash; a kosarad mindegyik esetben megmarad.</p>
        </div>

        <div class="mt-8 grid gap-5 lg:grid-cols-5 lg:items-start">
            <div class="animate-pop card relative overflow-hidden lg:col-span-3">
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

                <div class="relative flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent-50 text-accent-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 12H9m9 0l-3-3m3 3l-3 3" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-black text-gray-900">Bejelentkezés</h2>
                        <p class="text-sm font-medium text-gray-500">Van már fiókod? Lépj be, és a korábbi adataid is kitöltődnek.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login') }}" class="relative mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="redirect" value="checkout">

                    <div>
                        <label for="gate-email" class="field-label">Email</label>
                        <input id="gate-email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="input-field">
                    </div>

                    <div>
                        <label for="gate-password" class="field-label">Jelszó</label>
                        <input id="gate-password" name="password" type="password" required
                               class="input-field">
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-600">
                        <input type="checkbox" name="remember" class="checkbox-field">
                        Maradjak bejelentkezve
                    </label>

                    <button type="submit" class="btn-primary w-full">
                        Bejelentkezés
                    </button>
                </form>
            </div>

            <div class="flex flex-col gap-5 lg:col-span-2">
                <div class="animate-pop card card-hover relative overflow-hidden" style="animation-delay: 90ms">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-accent2-100 opacity-60"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent2-50 text-accent2-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3M4.5 19.5a4.5 4.5 0 119 0v.75h-9v-.75zM12 11.25a3.375 3.375 0 10-3.375-3.375A3.375 3.375 0 0012 11.25z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-black text-gray-900">Regisztráció</h2>
                            <p class="text-sm font-medium text-gray-500">Hozz létre fiókot, hogy nyomon követhesd a rendeléseidet.</p>
                        </div>
                    </div>
                    <a href="{{ route('register', ['redirect' => 'checkout']) }}" class="btn-primary btn-sm relative mt-5 w-full">
                        Regisztráció
                    </a>
                </div>

                <div class="animate-pop card card-hover relative overflow-hidden" style="animation-delay: 180ms">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-accent3-100 opacity-60"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent3-50 text-accent3-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-black text-gray-900">Vendégként</h2>
                            <p class="text-sm font-medium text-gray-500">Fiók nélkül is leadhatod a rendelésed, csak add meg a szállítási adataidat.</p>
                        </div>
                    </div>
                    <a href="{{ route('checkout.create') }}" class="btn-secondary btn-sm relative mt-5 w-full">
                        Folytatás regisztráció nélkül
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
