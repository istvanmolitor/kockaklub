@extends('layouts.app')

@section('title', 'Fiókom')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Fiókom</h1>

    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg p-4 mb-6">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.update') }}" class="space-y-6 mb-8">
        @csrf
        @method('PATCH')

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="font-semibold text-gray-900">Adataim</h2>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Név</label>
                <input id="name" name="name" type="text" value="{{ old('name', $customer->name ?? $user->name) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <p class="mt-1 text-sm text-gray-600">{{ $user->email }}</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="font-semibold text-gray-900">Szállítási adatok</h2>

            <div>
                <label for="shipping_name" class="block text-sm font-medium text-gray-700">Név</label>
                <input id="shipping_name" name="shipping_name" type="text"
                       value="{{ old('shipping_name', $customer->shipping_name ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="shipping_country" class="block text-sm font-medium text-gray-700">Ország</label>
                    <input id="shipping_country" name="shipping_country" type="text"
                           value="{{ old('shipping_country', $customer->shipping_country ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="shipping_city" class="block text-sm font-medium text-gray-700">Város</label>
                    <input id="shipping_city" name="shipping_city" type="text"
                           value="{{ old('shipping_city', $customer->shipping_city ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="shipping_zip" class="block text-sm font-medium text-gray-700">Irányítószám</label>
                    <input id="shipping_zip" name="shipping_zip" type="text"
                           value="{{ old('shipping_zip', $customer->shipping_zip ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="shipping_address" class="block text-sm font-medium text-gray-700">Cím</label>
                    <input id="shipping_address" name="shipping_address" type="text"
                           value="{{ old('shipping_address', $customer->shipping_address ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <h2 class="font-semibold text-gray-900">Számlázási adatok</h2>

            <div>
                <label for="billing_name" class="block text-sm font-medium text-gray-700">Név</label>
                <input id="billing_name" name="billing_name" type="text"
                       value="{{ old('billing_name', $customer->billing_name ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="billing_country" class="block text-sm font-medium text-gray-700">Ország</label>
                    <input id="billing_country" name="billing_country" type="text"
                           value="{{ old('billing_country', $customer->billing_country ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="billing_city" class="block text-sm font-medium text-gray-700">Város</label>
                    <input id="billing_city" name="billing_city" type="text"
                           value="{{ old('billing_city', $customer->billing_city ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="billing_zip" class="block text-sm font-medium text-gray-700">Irányítószám</label>
                    <input id="billing_zip" name="billing_zip" type="text"
                           value="{{ old('billing_zip', $customer->billing_zip ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="billing_address" class="block text-sm font-medium text-gray-700">Cím</label>
                    <input id="billing_address" name="billing_address" type="text"
                           value="{{ old('billing_address', $customer->billing_address ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
            </div>
        </div>

        <button type="submit"
                class="rounded-md bg-amber-600 px-5 py-3 text-white font-medium hover:bg-amber-700">
            Adatok mentése
        </button>
    </form>

    <h2 class="font-semibold text-gray-900 mb-4">Rendeléseim</h2>

    @if ($orders->isEmpty())
        <p class="text-gray-600">Még nem adtál le rendelést.</p>
    @else
        <div class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-200">
            @foreach ($orders as $order)
                <a href="{{ route('checkout.confirmation', $order) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('Y.m.d.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">{{ number_format($order->total, 0, ',', ' ') }} Ft</p>
                        <p class="text-sm text-gray-500">{{ $order->orderStatus->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
