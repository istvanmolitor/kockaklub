@extends('layouts.app')

@section('title', 'Email megerősítése')

@section('content')
    <div class="animate-pop relative mx-auto max-w-md overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 text-center shadow-sm">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>
        <h1 class="relative text-2xl font-black text-gray-900">Erősítsd meg az email címedet</h1>

        <p class="relative mt-4 text-sm font-medium text-gray-600">
            Köszönjük a regisztrációt! Mielőtt elkezdenéd, kérjük kattints a
            megerősítő linkre, amit az emailben küldtünk. Ha nem kaptad meg,
            szívesen küldünk egy újat.
        </p>

        @if (session('status') === 'verification-link-sent')
            <p class="relative mt-4 text-sm font-bold text-emerald-600">
                Új megerősítő linket küldtünk a regisztrációkor megadott email címre.
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="relative mt-6">
            @csrf
            <button type="submit" class="btn-primary w-full">
                Megerősítő link újraküldése
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="relative mt-4">
            @csrf
            <button type="submit" class="text-sm font-semibold text-gray-500 hover:underline">Kijelentkezés</button>
        </form>
    </div>
@endsection
