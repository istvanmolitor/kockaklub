@extends('layouts.account')

@section('title', 'Fiókom')

@section('account-content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Fiókom</h1>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.update') }}" class="mb-8 space-y-6">
        @csrf
        @method('PATCH')

        <div class="card space-y-4">
            <h2 class="text-lg font-black text-gray-900">Adataim</h2>

            <div>
                <label for="name" class="field-label">Név</label>
                <input id="name" name="name" type="text" value="{{ old('name', $customer->name ?? $user->name) }}" required
                       class="input-field">
            </div>

            <div>
                <label class="field-label">Email</label>
                <p class="mt-1.5 text-sm font-medium text-gray-600">{{ $user->email }}</p>
            </div>

            <div>
                <label for="phone" class="field-label">Telefon</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone ?? '') }}"
                       class="input-field">
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-lg font-black text-gray-900">Szállítási adatok</h2>

            <div>
                <label for="shipping_name" class="field-label">Név</label>
                <input id="shipping_name" name="shipping_name" type="text"
                       value="{{ old('shipping_name', $customer->shipping_name ?? '') }}"
                       class="input-field">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="shipping_country" class="field-label">Ország</label>
                    <input id="shipping_country" name="shipping_country" type="text"
                           value="{{ old('shipping_country', $customer->shipping_country ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="shipping_city" class="field-label">Város</label>
                    <input id="shipping_city" name="shipping_city" type="text"
                           value="{{ old('shipping_city', $customer->shipping_city ?? '') }}"
                           class="input-field">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="shipping_zip" class="field-label">Irányítószám</label>
                    <input id="shipping_zip" name="shipping_zip" type="text"
                           value="{{ old('shipping_zip', $customer->shipping_zip ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="shipping_address" class="field-label">Cím</label>
                    <input id="shipping_address" name="shipping_address" type="text"
                           value="{{ old('shipping_address', $customer->shipping_address ?? '') }}"
                           class="input-field">
                </div>
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-lg font-black text-gray-900">Számlázási adatok</h2>

            <div>
                <label for="billing_name" class="field-label">Név</label>
                <input id="billing_name" name="billing_name" type="text"
                       value="{{ old('billing_name', $customer->billing_name ?? '') }}"
                       class="input-field">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="billing_country" class="field-label">Ország</label>
                    <input id="billing_country" name="billing_country" type="text"
                           value="{{ old('billing_country', $customer->billing_country ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="billing_city" class="field-label">Város</label>
                    <input id="billing_city" name="billing_city" type="text"
                           value="{{ old('billing_city', $customer->billing_city ?? '') }}"
                           class="input-field">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="billing_zip" class="field-label">Irányítószám</label>
                    <input id="billing_zip" name="billing_zip" type="text"
                           value="{{ old('billing_zip', $customer->billing_zip ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="billing_address" class="field-label">Cím</label>
                    <input id="billing_address" name="billing_address" type="text"
                           value="{{ old('billing_address', $customer->billing_address ?? '') }}"
                           class="input-field">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            Adatok mentése
        </button>
    </form>
@endsection
