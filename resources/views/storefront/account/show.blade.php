@extends('layouts.app')

@section('title', 'Fiókom')

@section('content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Fiókom</h1>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="font-semibold text-gray-900 mb-2">Adataim</h2>
        <p class="text-sm text-gray-600">Név: {{ $customer->name ?? $user->name }}</p>
        <p class="text-sm text-gray-600">Email: {{ $user->email }}</p>
        @if ($customer?->phone)
            <p class="text-sm text-gray-600">Telefon: {{ $customer->phone }}</p>
        @endif
    </div>

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
