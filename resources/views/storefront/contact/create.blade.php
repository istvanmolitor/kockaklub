@extends('layouts.app')

@section('title', 'Kapcsolat')

@section('content')
    @php
        $customer = auth()->user()?->customer;
    @endphp

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Kapcsolat</h1>

    <div class="max-w-xl">
        <form method="POST" action="{{ route('contact.store') }}" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Név</label>
                <input id="name" name="name" type="text" value="{{ old('name', $customer->name ?? '') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Telefonszám</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Üzenet</label>
                <textarea id="message" name="message" rows="6" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="rounded-md bg-amber-600 px-5 py-2.5 text-white font-medium hover:bg-amber-700">
                Üzenet küldése
            </button>
        </form>
    </div>
@endsection
