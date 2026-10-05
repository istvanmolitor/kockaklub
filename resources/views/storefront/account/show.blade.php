@extends('layouts.account')

@section('title', 'Fiókom')
@section('robots', 'noindex,nofollow')

@section('account-content')
    @php
        $defaultCountryId = $countries->firstWhere('code', 'HU')?->id;
    @endphp

    <h1 class="mb-6 text-2xl font-black text-gray-900">Fiókom</h1>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.update') }}" class="mb-8 space-y-6">
        @csrf
        @method('PATCH')

        <div class="card animate-pop relative overflow-hidden p-8">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

            <div class="relative space-y-4">
                <h2 class="text-lg font-black text-gray-900">Adataim</h2>

                <div>
                    <label for="name" class="field-label">Név</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $customer->name ?? $user->name) }}" required
                           class="input-field @error('name') !border-rose-400 focus:!border-rose-500 focus:!ring-rose-500/15 @enderror">
                    @error('name')
                        <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
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
        </div>

        <div class="card animate-pop relative overflow-hidden p-8" style="animation-delay: 90ms">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

            <div class="relative space-y-4">
                <h2 class="text-lg font-black text-gray-900">Szállítási adatok</h2>

                <div>
                    <label for="shipping_name" class="field-label">Név</label>
                    <input id="shipping_name" name="shipping_name" type="text"
                           value="{{ old('shipping_name', $customer->shipping_name ?? '') }}"
                           class="input-field">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_country_id" class="field-label">Ország</label>
                        <select id="shipping_country_id" name="shipping_country_id" class="select-field mt-1.5 block w-full">
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}" @selected((int) old('shipping_country_id', $customer->shipping_country_id ?? $defaultCountryId) === $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="shipping_zip" class="field-label">Irányítószám</label>
                        <input id="shipping_zip" name="shipping_zip" type="text" data-zip-input data-country-input="shipping_country_id" data-city-target="shipping_city"
                               value="{{ old('shipping_zip', $customer->shipping_zip ?? '') }}"
                               class="input-field">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_city" class="field-label">Város</label>
                        <input id="shipping_city" name="shipping_city" type="text"
                               value="{{ old('shipping_city', $customer->shipping_city ?? '') }}"
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
        </div>

        <div class="card animate-pop p-8 space-y-4" style="animation-delay: 180ms">
            <h2 class="text-lg font-black text-gray-900">Számlázási adatok</h2>

            <div>
                <label for="billing_name" class="field-label">Név</label>
                <input id="billing_name" name="billing_name" type="text"
                       value="{{ old('billing_name', $customer->billing_name ?? '') }}"
                       class="input-field">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="billing_country_id" class="field-label">Ország</label>
                    <select id="billing_country_id" name="billing_country_id" class="select-field mt-1.5 block w-full">
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected((int) old('billing_country_id', $customer->billing_country_id ?? $defaultCountryId) === $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="billing_zip" class="field-label">Irányítószám</label>
                    <input id="billing_zip" name="billing_zip" type="text" data-zip-input data-country-input="billing_country_id" data-city-target="billing_city"
                           value="{{ old('billing_zip', $customer->billing_zip ?? '') }}"
                           class="input-field">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="billing_city" class="field-label">Város</label>
                    <input id="billing_city" name="billing_city" type="text"
                           value="{{ old('billing_city', $customer->billing_city ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="billing_address" class="field-label">Cím</label>
                    <input id="billing_address" name="billing_address" type="text"
                           value="{{ old('billing_address', $customer->billing_address ?? '') }}"
                           class="input-field">
                </div>
            </div>

            <div>
                <label for="billing_tax_number" class="field-label">Adószám (cégeknek, opcionális)</label>
                <input id="billing_tax_number" name="billing_tax_number" type="text"
                       value="{{ old('billing_tax_number', $customer->billing_tax_number ?? '') }}"
                       class="input-field">
            </div>
        </div>

        <button type="submit" class="btn-primary">
            Adatok mentése
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
