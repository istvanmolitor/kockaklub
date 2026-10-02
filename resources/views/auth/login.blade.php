@extends('layouts.app')

@section('title', 'Bejelentkezés')

@section('content')
    <div class="animate-pop relative mx-auto max-w-md overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>
        <h1 class="relative text-2xl font-black text-gray-900">Bejelentkezés</h1>

        <form method="POST" action="{{ route('login') }}" class="relative mt-6 space-y-4">
            @csrf
            @if (request('redirect') === 'checkout')
                <input type="hidden" name="redirect" value="checkout">
            @endif

            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="input-field">
            </div>

            <div>
                <label for="password" class="field-label">Jelszó</label>
                <input id="password" name="password" type="password" required
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

        <p class="relative mt-5 text-sm font-medium text-gray-600">
            Nincs még fiókod? <a href="{{ route('register', request('redirect') === 'checkout' ? ['redirect' => 'checkout'] : []) }}" class="font-bold text-accent-600 hover:underline">Regisztrálj</a>
        </p>
    </div>
@endsection
