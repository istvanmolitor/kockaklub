@extends('layouts.app')

@section('title', 'Email megerősítése')

@section('content')
    <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg p-8 text-center">
        <h1 class="text-2xl font-semibold mb-4">Erősítsd meg az email címedet</h1>

        <p class="text-gray-600 mb-6">
            Köszönjük a regisztrációt! Mielőtt elkezdenéd, kérjük kattints a
            megerősítő linkre, amit az emailben küldtünk. Ha nem kaptad meg,
            szívesen küldünk egy újat.
        </p>

        @if (session('status') === 'verification-link-sent')
            <p class="mb-4 text-sm font-medium text-green-600">
                Új megerősítő linket küldtünk a regisztrációkor megadott email címre.
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="rounded-md bg-amber-600 px-4 py-2 text-white font-medium hover:bg-amber-700">
                Megerősítő link újraküldése
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline">Kijelentkezés</button>
        </form>
    </div>
@endsection
