@extends('layouts.app')

@section('title', 'Új jelszó beállítása')

@section('content')
    <div class="animate-pop relative mx-auto max-w-md overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm">
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>
        <h1 class="relative text-2xl font-black text-gray-900">Új jelszó beállítása</h1>

        <form method="POST" action="{{ route('password.store') }}" class="relative mt-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="field-label">Email</label>
                <p class="mt-1.5 text-sm font-medium text-gray-600">{{ $email }}</p>
            </div>

            <div>
                <label for="password" class="field-label">Új jelszó</label>
                <input id="password" name="password" type="password" required autofocus
                       class="input-field">
            </div>

            <div>
                <label for="password_confirmation" class="field-label">Új jelszó megerősítése</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="input-field">
            </div>

            <button type="submit" class="btn-primary w-full">
                Jelszó beállítása
            </button>
        </form>
    </div>
@endsection
