@extends('layouts.account')

@section('title', 'Jelszó módosítása')

@section('account-content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Jelszó módosítása</h1>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="card animate-pop relative overflow-hidden p-8">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

            <div class="relative space-y-4">
                <div>
                    <label for="password" class="field-label">Új jelszó</label>
                    <input id="password" name="password" type="password" required autofocus
                           class="input-field @error('password') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="field-label">Új jelszó megerősítése</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="input-field">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            Jelszó módosítása
        </button>
    </form>
@endsection
