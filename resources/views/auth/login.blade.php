@extends('layouts.app')

@section('title', 'Bejelentkezés')

@section('content')
    <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg p-8">
        <h1 class="text-2xl font-semibold mb-6">Bejelentkezés</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Jelszó</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                Maradjak bejelentkezve
            </label>

            <button type="submit"
                    class="w-full rounded-md bg-amber-600 px-4 py-2 text-white font-medium hover:bg-amber-700">
                Bejelentkezés
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Nincs még fiókod? <a href="{{ route('register') }}" class="text-amber-600 hover:underline">Regisztrálj</a>
        </p>
    </div>
@endsection
