@extends('layouts.app')

@section('title', 'Regisztráció')

@section('content')
    <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg p-8">
        <h1 class="text-2xl font-semibold mb-6">Regisztráció</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Név</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Jelszó</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Jelszó megerősítése</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <button type="submit"
                    class="w-full rounded-md bg-amber-600 px-4 py-2 text-white font-medium hover:bg-amber-700">
                Regisztráció
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Már van fiókod? <a href="{{ route('login') }}" class="text-amber-600 hover:underline">Jelentkezz be</a>
        </p>
    </div>
@endsection
