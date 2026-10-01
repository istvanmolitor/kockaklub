@extends('layouts.app')

@section('title', 'Pénztár')

@section('content')
    @php
        $selectedShippingMethodId = (int) old('shipping_method_id', $shippingMethods->first()->id);
        $oldPaymentMethodId = old('payment_method_id');
    @endphp

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Pénztár</h1>

    <div class="grid md:grid-cols-3 gap-10">
        <form method="POST" action="{{ route('checkout.store') }}" class="md:col-span-2 space-y-6">
            @csrf

            @guest
                <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900">Kapcsolattartó adatok</h2>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Név</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                </div>
            @endguest

            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
                <h2 class="font-semibold text-gray-900">Szállítási adatok</h2>

                <div>
                    <label for="shipping_name" class="block text-sm font-medium text-gray-700">Átvevő neve</label>
                    <input id="shipping_name" name="shipping_name" type="text"
                           value="{{ old('shipping_name', $customer->name ?? '') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="shipping_phone" class="block text-sm font-medium text-gray-700">Telefonszám</label>
                    <input id="shipping_phone" name="shipping_phone" type="text"
                           value="{{ old('shipping_phone', $customer->phone ?? '') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label for="shipping_address" class="block text-sm font-medium text-gray-700">Szállítási cím</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('shipping_address') }}</textarea>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-3">
                <h2 class="font-semibold text-gray-900">Szállítási mód</h2>

                @foreach ($shippingMethods as $shippingMethod)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="radio" name="shipping_method_id" value="{{ $shippingMethod->id }}"
                               data-shipping-method-option
                               {{ $selectedShippingMethodId === $shippingMethod->id ? 'checked' : '' }}
                               class="mt-1 border-gray-300">
                        <span>
                            {{ $shippingMethod->name }}
                            @if ($shippingMethod->description)
                                <span class="block text-gray-500">{{ $shippingMethod->description }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-3">
                <h2 class="font-semibold text-gray-900">Fizetési mód</h2>

                @foreach ($shippingMethods as $shippingMethod)
                    <div data-payment-methods-for="{{ $shippingMethod->id }}"
                         class="space-y-3 {{ $selectedShippingMethodId === $shippingMethod->id ? '' : 'hidden' }}">
                        @foreach ($shippingMethod->paymentMethods as $paymentMethod)
                            <label class="flex items-start gap-2 text-sm">
                                <input type="radio" name="payment_method_id" value="{{ $paymentMethod->id }}"
                                       {{ $oldPaymentMethodId
                                            ? ($oldPaymentMethodId == $paymentMethod->id ? 'checked' : '')
                                            : ($selectedShippingMethodId === $shippingMethod->id && $loop->first ? 'checked' : '') }}
                                       class="mt-1 border-gray-300">
                                <span>
                                    {{ $paymentMethod->name }}
                                    @if ($paymentMethod->description)
                                        <span class="block text-gray-500">{{ $paymentMethod->description }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <button type="submit"
                    class="w-full rounded-md bg-amber-600 px-5 py-3 text-white font-medium hover:bg-amber-700">
                Rendelés leadása
            </button>
        </form>

        <div class="bg-white border border-gray-200 rounded-lg p-6 h-fit">
            <h2 class="font-semibold text-gray-900 mb-4">Rendelés összegzés</h2>

            <ul class="divide-y divide-gray-200 text-sm">
                @foreach ($cart->items as $item)
                    <li class="py-2 flex justify-between">
                        <span>{{ $item->product->name }} &times; {{ $item->quantity }}</span>
                        <span>{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between font-semibold">
                <span>Összesen</span>
                <span>{{ number_format($cart->items->sum(fn ($item) => $item->lineTotal()), 0, ',', ' ') }} Ft</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var shippingInputs = document.querySelectorAll('[data-shipping-method-option]');
            var paymentGroups = document.querySelectorAll('[data-payment-methods-for]');

            function updatePaymentGroups() {
                var selected = document.querySelector('[data-shipping-method-option]:checked');
                var selectedId = selected ? selected.value : null;

                paymentGroups.forEach(function (group) {
                    var isVisible = group.dataset.paymentMethodsFor === selectedId;
                    group.classList.toggle('hidden', !isVisible);

                    if (isVisible && !group.querySelector('input[type="radio"]:checked')) {
                        var firstRadio = group.querySelector('input[type="radio"]');
                        if (firstRadio) {
                            firstRadio.checked = true;
                        }
                    }
                });
            }

            shippingInputs.forEach(function (input) {
                input.addEventListener('change', updatePaymentGroups);
            });
        });
    </script>
@endsection
