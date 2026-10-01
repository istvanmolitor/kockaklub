@extends('layouts.app')

@section('title', 'Pénztár')

@section('content')
    @php
        $selectedShippingMethodId = (int) old('shipping_method_id', $shippingMethods->first()->id);
        $oldPaymentMethodId = old('payment_method_id');
        $cartTotal = $cart->items->sum(fn ($item) => $item->lineTotal());
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

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700">Ország</label>
                        <input id="shipping_country" name="shipping_country" type="text"
                               value="{{ old('shipping_country', $customer->shipping_country ?? 'Magyarország') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700">Város</label>
                        <input id="shipping_city" name="shipping_city" type="text"
                               value="{{ old('shipping_city', $customer->shipping_city ?? '') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="shipping_zip" class="block text-sm font-medium text-gray-700">Irányítószám</label>
                        <input id="shipping_zip" name="shipping_zip" type="text"
                               value="{{ old('shipping_zip', $customer->shipping_zip ?? '') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700">Cím</label>
                        <input id="shipping_address" name="shipping_address" type="text"
                               value="{{ old('shipping_address', $customer->shipping_address ?? '') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
                <h2 class="font-semibold text-gray-900">Számlázási adatok</h2>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="1"
                           {{ old('billing_same_as_shipping', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    A számlázási cím megegyezik a szállítási címmel
                </label>

                <div id="billing-fields" class="space-y-4 {{ old('billing_same_as_shipping', '1') ? 'hidden' : '' }}">
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
                                   value="{{ old('billing_country', $customer->billing_country ?? 'Magyarország') }}"
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
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-3">
                <h2 class="font-semibold text-gray-900">Szállítási mód</h2>

                @foreach ($shippingMethods as $shippingMethod)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="radio" name="shipping_method_id" value="{{ $shippingMethod->id }}"
                               data-shipping-method-option
                               data-cost="{{ $shippingMethod->cost }}"
                               {{ $selectedShippingMethodId === $shippingMethod->id ? 'checked' : '' }}
                               class="mt-1 border-gray-300">
                        <span>
                            {{ $shippingMethod->name }}
                            &mdash;
                            {{ $shippingMethod->cost > 0 ? number_format($shippingMethod->cost, 0, ',', ' ').' Ft' : 'Díjtalan' }}
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
                                       data-payment-method-option
                                       data-cost="{{ $paymentMethod->cost }}"
                                       {{ $oldPaymentMethodId
                                            ? ($oldPaymentMethodId == $paymentMethod->id ? 'checked' : '')
                                            : ($selectedShippingMethodId === $shippingMethod->id && $loop->first ? 'checked' : '') }}
                                       class="mt-1 border-gray-300">
                                <span>
                                    {{ $paymentMethod->name }}
                                    &mdash;
                                    {{ $paymentMethod->cost > 0 ? number_format($paymentMethod->cost, 0, ',', ' ').' Ft' : 'Díjtalan' }}
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

            <div class="mt-4 pt-4 border-t border-gray-200 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span>Részösszeg</span>
                    <span data-summary-subtotal>{{ number_format($cartTotal, 0, ',', ' ') }} Ft</span>
                </div>
                <div class="flex justify-between">
                    <span>Szállítás</span>
                    <span data-summary-shipping-cost>0 Ft</span>
                </div>
                <div class="flex justify-between">
                    <span>Fizetési díj</span>
                    <span data-summary-payment-cost>0 Ft</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between font-semibold">
                <span>Összesen</span>
                <span data-summary-total>{{ number_format($cartTotal, 0, ',', ' ') }} Ft</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cartSubtotal = {{ (int) $cartTotal }};
            var shippingInputs = document.querySelectorAll('[data-shipping-method-option]');
            var paymentGroups = document.querySelectorAll('[data-payment-methods-for]');

            function formatFt(amount) {
                return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ft';
            }

            function updateSummary() {
                var selectedShipping = document.querySelector('[data-shipping-method-option]:checked');
                var selectedPayment = document.querySelector('[data-payment-method-option]:checked');

                var shippingCost = selectedShipping ? parseInt(selectedShipping.dataset.cost, 10) : 0;
                var paymentCost = selectedPayment ? parseInt(selectedPayment.dataset.cost, 10) : 0;

                document.querySelector('[data-summary-shipping-cost]').textContent = formatFt(shippingCost);
                document.querySelector('[data-summary-payment-cost]').textContent = formatFt(paymentCost);
                document.querySelector('[data-summary-total]').textContent = formatFt(cartSubtotal + shippingCost + paymentCost);
            }

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

                updateSummary();
            }

            shippingInputs.forEach(function (input) {
                input.addEventListener('change', updatePaymentGroups);
            });

            document.querySelectorAll('[data-payment-method-option]').forEach(function (input) {
                input.addEventListener('change', updateSummary);
            });

            updateSummary();

            var billingCheckbox = document.getElementById('billing_same_as_shipping');
            var billingFields = document.getElementById('billing-fields');

            billingCheckbox.addEventListener('change', function () {
                billingFields.classList.toggle('hidden', billingCheckbox.checked);
            });
        });
    </script>
@endsection
