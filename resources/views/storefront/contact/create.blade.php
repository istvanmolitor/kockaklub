@extends('layouts.app')

@section('title', 'Kapcsolat')

@section('content')
    @php
        $customer = auth()->user()?->customer;
    @endphp

    <h1 class="mb-6 text-2xl font-black text-gray-900">Kapcsolat</h1>

    <div class="grid gap-8 md:grid-cols-5">
        <div class="animate-pop relative overflow-hidden rounded-3xl border-2 border-gray-100 bg-white p-8 shadow-sm md:col-span-3">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-accent-200 to-accent3-200 opacity-50"></div>

            <h2 class="relative text-xl font-black text-gray-900">Írj nekünk</h2>
            <p class="relative mt-1 text-sm font-medium text-gray-500">Örömmel válaszolunk minden kérdésedre.</p>

            <form method="POST" action="{{ route('contact.store') }}" class="relative mt-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="field-label">Név</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $customer->name ?? '') }}" required
                           class="input-field">
                </div>

                <div>
                    <label for="email" class="field-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" required
                           class="input-field">
                </div>

                <div>
                    <label for="phone" class="field-label">Telefonszám</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone ?? '') }}"
                           class="input-field">
                </div>

                <div>
                    <label for="message" class="field-label">Üzenet</label>
                    <textarea id="message" name="message" rows="6" required
                              class="input-field">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn-primary w-full">
                    Üzenet küldése
                </button>
            </form>
        </div>

        <div class="space-y-6 md:col-span-2">
            <div class="card space-y-5">
                <h2 class="text-lg font-black text-gray-900">Elérhetőségeink</h2>

                @if (setting('contact_address'))
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Cím</p>
                            <p class="text-sm font-medium text-gray-500">{{ setting('contact_address') }}</p>
                        </div>
                    </div>
                @endif

                @if (setting('contact_phone'))
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent2-50 text-accent2-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a11.25 11.25 0 01-6.248-6.248l1.293-.97a1.125 1.125 0 00.417-1.173L8.963 3.852a1.125 1.125 0 00-1.091-.852H6.5A2.25 2.25 0 004.25 5.25v1.5z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Telefon</p>
                            <a href="tel:{{ setting('contact_phone') }}" class="text-sm font-medium text-gray-500 hover:text-accent-600">{{ setting('contact_phone') }}</a>
                        </div>
                    </div>
                @endif

                @if (setting('contact_email'))
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent3-50 text-accent3-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Email</p>
                            <a href="mailto:{{ setting('contact_email') }}" class="text-sm font-medium text-gray-500 hover:text-accent-600">{{ setting('contact_email') }}</a>
                        </div>
                    </div>
                @endif
            </div>

            @if (setting('contact_address'))
                <div class="overflow-hidden rounded-3xl border-2 border-gray-100 shadow-sm">
                    <iframe
                        src="https://www.google.com/maps?q={{ urlencode(setting('contact_address')) }}&output=embed"
                        class="h-72 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="Térkép – {{ setting('contact_address') }}"></iframe>
                </div>
            @endif
        </div>
    </div>
@endsection
