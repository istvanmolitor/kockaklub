@extends('layouts.app')

@section('title', 'Pénztár')
@section('robots', 'noindex,nofollow')

@section('content')
    @php
        $selectedShippingMethodId = (int) old('shipping_method_id', $shippingMethods->first()->id);
        $oldPaymentMethodId = old('payment_method_id');
        $cartTotal = $cart->items->sum(fn ($item) => $item->lineTotal());
        $defaultCountryId = $countries->firstWhere('code', 'HU')?->id;
    @endphp

    <h1 class="mb-6 text-2xl font-black text-gray-900">Pénztár</h1>

    <div class="grid gap-10 md:grid-cols-3 md:items-start">
        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="md:col-span-2 space-y-6">
            @csrf

            @guest
                <div class="card animate-pop relative overflow-hidden p-8">
                    <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

                    <div class="relative space-y-4">
                        <h2 class="text-lg font-black text-gray-900">Kapcsolattartó adatok</h2>

                        <div>
                            <label for="name" class="field-label">Név</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}"
                                   class="input-field @error('name') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @error('name')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="field-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                   class="input-field @error('email') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @error('email')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endguest

            <div class="card animate-pop relative overflow-hidden p-8" style="animation-delay: 90ms">
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

                <div class="relative space-y-4">
                <h2 class="text-lg font-black text-gray-900">Szállítási adatok</h2>

                <div>
                    <label for="shipping_name" class="field-label">Átvevő neve</label>
                    <input id="shipping_name" name="shipping_name" type="text"
                           value="{{ old('shipping_name', $customer->name ?? '') }}"
                           class="input-field @error('shipping_name') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                    @error('shipping_name')
                        <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shipping_phone" class="field-label">Telefonszám</label>
                    <input id="shipping_phone" name="shipping_phone" type="text"
                           value="{{ old('shipping_phone', $customer->phone ?? '') }}"
                           class="input-field @error('shipping_phone') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                    @error('shipping_phone')
                        <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_country_id" class="field-label">Ország</label>
                        <select id="shipping_country_id" name="shipping_country_id"
                                class="select-field mt-1.5 block w-full @error('shipping_country_id') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected((int) old('shipping_country_id', $customer->shipping_country_id ?? $defaultCountryId) === $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('shipping_country_id')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_zip" class="field-label">Irányítószám</label>
                        <input id="shipping_zip" name="shipping_zip" type="text" data-zip-input data-country-input="shipping_country_id" data-city-target="shipping_city"
                               value="{{ old('shipping_zip', $customer->shipping_zip ?? '') }}"
                               class="input-field @error('shipping_zip') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                        @error('shipping_zip')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_city" class="field-label">Város</label>
                        <input id="shipping_city" name="shipping_city" type="text"
                               value="{{ old('shipping_city', $customer->shipping_city ?? '') }}"
                               class="input-field @error('shipping_city') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                        @error('shipping_city')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_address" class="field-label">Cím</label>
                        <input id="shipping_address" name="shipping_address" type="text"
                               value="{{ old('shipping_address', $customer->shipping_address ?? '') }}"
                               class="input-field @error('shipping_address') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                        @error('shipping_address')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                </div>
            </div>

            <div class="card animate-pop p-8 space-y-4" style="animation-delay: 180ms">
                <h2 class="text-lg font-black text-gray-900">Számlázási adatok</h2>

                <label class="flex items-center gap-2 text-sm font-medium text-gray-600">
                    <input type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="1"
                           {{ old('billing_same_as_shipping', '1') ? 'checked' : '' }}
                           class="checkbox-field">
                    A számlázási cím megegyezik a szállítási címmel
                </label>

                <div>
                    <label for="billing_tax_number" class="field-label">Adószám (cégeknek, opcionális)</label>
                    <input id="billing_tax_number" name="billing_tax_number" type="text"
                           value="{{ old('billing_tax_number', $customer->billing_tax_number ?? '') }}"
                           class="input-field @error('billing_tax_number') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                    @error('billing_tax_number')
                        <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="billing-fields" class="space-y-4 {{ old('billing_same_as_shipping', '1') ? 'hidden' : '' }}">
                    <div>
                        <label for="billing_name" class="field-label">Név</label>
                        <input id="billing_name" name="billing_name" type="text"
                               value="{{ old('billing_name', $customer->billing_name ?? '') }}"
                               class="input-field @error('billing_name') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                        @error('billing_name')
                            <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="billing_country_id" class="field-label">Ország</label>
                            <select id="billing_country_id" name="billing_country_id"
                                    class="select-field mt-1.5 block w-full @error('billing_country_id') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((int) old('billing_country_id', $customer->billing_country_id ?? $defaultCountryId) === $country->id)>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('billing_country_id')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_zip" class="field-label">Irányítószám</label>
                            <input id="billing_zip" name="billing_zip" type="text" data-zip-input data-country-input="billing_country_id" data-city-target="billing_city"
                                   value="{{ old('billing_zip', $customer->billing_zip ?? '') }}"
                                   class="input-field @error('billing_zip') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @error('billing_zip')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="billing_city" class="field-label">Város</label>
                            <input id="billing_city" name="billing_city" type="text"
                                   value="{{ old('billing_city', $customer->billing_city ?? '') }}"
                                   class="input-field @error('billing_city') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @error('billing_city')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_address" class="field-label">Cím</label>
                            <input id="billing_address" name="billing_address" type="text"
                                   value="{{ old('billing_address', $customer->billing_address ?? '') }}"
                                   class="input-field @error('billing_address') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                            @error('billing_address')
                                <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card animate-pop p-8 space-y-3" style="animation-delay: 270ms">
                <h2 class="text-lg font-black text-gray-900">Szállítási mód</h2>

                @foreach ($shippingMethods as $shippingMethod)
                    <label class="flex items-start gap-3 rounded-2xl border-2 border-gray-100 p-3 text-sm font-medium transition hover:border-accent-200 hover:bg-accent-50/50">
                        <input type="radio" name="shipping_method_id" value="{{ $shippingMethod->id }}"
                               data-shipping-method-option
                               data-cost="{{ $shippingMethod->cost }}"
                               {{ $selectedShippingMethodId === $shippingMethod->id ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 border-2 border-gray-300 text-accent-600 focus:ring-4 focus:ring-accent-500/15">
                        <span>
                            <span class="font-bold text-gray-900">{{ $shippingMethod->name }}</span>
                            &mdash;
                            {{ $shippingMethod->cost > 0 ? number_format($shippingMethod->cost, 0, ',', ' ').' Ft' : 'Díjtalan' }}
                            @if ($shippingMethod->description)
                                <span class="block text-gray-500">{{ $shippingMethod->description }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach

                @error('shipping_method_id')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="card animate-pop p-8 space-y-3" style="animation-delay: 360ms">
                <h2 class="text-lg font-black text-gray-900">Fizetési mód</h2>

                @foreach ($shippingMethods as $shippingMethod)
                    <div data-payment-methods-for="{{ $shippingMethod->id }}"
                         class="space-y-3 {{ $selectedShippingMethodId === $shippingMethod->id ? '' : 'hidden' }}">
                        @foreach ($shippingMethod->paymentMethods as $paymentMethod)
                            <label class="flex items-start gap-3 rounded-2xl border-2 border-gray-100 p-3 text-sm font-medium transition hover:border-accent-200 hover:bg-accent-50/50">
                                <input type="radio" name="payment_method_id" value="{{ $paymentMethod->id }}"
                                       data-payment-method-option
                                       data-cost="{{ $paymentMethod->cost }}"
                                       {{ $oldPaymentMethodId
                                            ? ($oldPaymentMethodId == $paymentMethod->id ? 'checked' : '')
                                            : ($selectedShippingMethodId === $shippingMethod->id && $loop->first ? 'checked' : '') }}
                                       class="mt-1 h-4 w-4 border-2 border-gray-300 text-accent-600 focus:ring-4 focus:ring-accent-500/15">
                                <span>
                                    <span class="font-bold text-gray-900">{{ $paymentMethod->name }}</span>
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

                @error('payment_method_id')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

        </form>

        <div class="animate-pop relative overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm md:sticky md:top-24" style="animation-delay: 90ms">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

            <h2 class="relative mb-4 text-lg font-black text-gray-900">Rendelés összegzés</h2>

            <ul class="relative divide-y divide-gray-100 text-sm">
                @foreach ($cart->items as $item)
                    <li class="flex justify-between py-2 font-medium text-gray-700">
                        <span>{{ $item->product->name }} &times; {{ $item->quantity }}</span>
                        <span>{{ number_format($item->lineTotal(), 0, ',', ' ') }} Ft</span>
                    </li>
                @endforeach
            </ul>

            <div class="relative mt-4 space-y-1 border-t border-gray-100 pt-4 text-sm font-medium text-gray-600">
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

            <div class="relative mt-4 flex justify-between border-t border-gray-100 pt-4 font-black text-gray-900">
                <span>Összesen</span>
                <span data-summary-total>{{ number_format($cartTotal, 0, ',', ' ') }} Ft</span>
            </div>

            <button type="submit" form="checkout-form" class="btn-primary relative mt-6 w-full">
                Rendelés leadása
            </button>
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

            var postalCodesUrlTemplate = @js(route('postal-codes.index', ['country' => '__COUNTRY__']));
            var postalCodeMaps = {};

            function loadPostalCodes(countryId) {
                if (!countryId) {
                    return Promise.resolve({});
                }

                if (!postalCodeMaps[countryId]) {
                    postalCodeMaps[countryId] = fetch(postalCodesUrlTemplate.replace('__COUNTRY__', countryId))
                        .then(function (response) { return response.json(); })
                        .catch(function () { return {}; });
                }

                return postalCodeMaps[countryId];
            }

            document.querySelectorAll('[data-zip-input]').forEach(function (zipInput) {
                var countrySelect = document.getElementById(zipInput.dataset.countryInput);
                var cityInput = document.getElementById(zipInput.dataset.cityTarget);

                if (!countrySelect || !cityInput) {
                    return;
                }

                countrySelect.addEventListener('change', function () {
                    loadPostalCodes(countrySelect.value);
                });

                zipInput.addEventListener('focus', function () {
                    loadPostalCodes(countrySelect.value);
                }, { once: true });

                zipInput.addEventListener('input', function () {
                    var zip = zipInput.value.trim();
                    if (!zip) {
                        return;
                    }

                    loadPostalCodes(countrySelect.value).then(function (map) {
                        if (map[zip]) {
                            cityInput.value = map[zip];
                        }
                    });
                });
            });
        });
    </script>
@endsection
