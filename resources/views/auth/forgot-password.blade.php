@extends('layouts.app')

@section('title', 'Elfelejtett jelszó')
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="animate-pop relative mx-auto max-w-md overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>
        <h1 class="relative text-2xl font-black text-gray-900">Elfelejtett jelszó</h1>
        <p class="relative mt-2 text-sm font-medium text-gray-600">
            Add meg az email címedet, és küldünk egy linket, amivel új jelszót állíthatsz be.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="relative mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="input-field">
            </div>

            <button type="submit" class="btn-primary w-full">
                Visszaállítási link küldése
            </button>
        </form>

        <p class="relative mt-5 text-sm font-medium text-gray-600">
            <a href="{{ route('login') }}" class="font-bold text-accent-600 hover:underline">Vissza a bejelentkezéshez</a>
        </p>
    </div>
@endsection
