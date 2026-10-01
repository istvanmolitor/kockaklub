@extends('layouts.app')

@section('title', 'Rendelés visszaigazolás')

@section('content')
    <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-2">Köszönjük a rendelésed!</h1>
        <p class="text-gray-600 mb-6">Rendelésszám: <strong>{{ $order->order_number }}</strong></p>

        <ul class="divide-y divide-gray-200 text-sm mb-6">
            @foreach ($order->items as $item)
                <li class="py-2 flex justify-between">
                    <span>{{ $item->product_name }} &times; {{ $item->quantity }}</span>
                    <span>{{ number_format($item->line_total, 0, ',', ' ') }} Ft</span>
                </li>
            @endforeach
        </ul>

        <dl class="text-sm text-gray-600 space-y-1 mb-4">
            <div class="flex justify-between"><dt>Részösszeg</dt><dd>{{ number_format($order->subtotal, 0, ',', ' ') }} Ft</dd></div>
            <div class="flex justify-between"><dt>Szállítás ({{ $order->shippingMethod->name }})</dt><dd>{{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft</dd></div>
            <div class="flex justify-between"><dt>Fizetési díj ({{ $order->paymentMethod->name }})</dt><dd>{{ number_format($order->payment_cost, 0, ',', ' ') }} Ft</dd></div>
        </dl>

        <div class="flex justify-between font-semibold text-lg mb-6">
            <span>Végösszeg</span>
            <span>{{ number_format($order->total, 0, ',', ' ') }} Ft</span>
        </div>

        <dl class="text-sm text-gray-600 space-y-1 mb-6">
            <div>
                <dt class="inline font-medium">Szállítási cím: </dt>
                <dd class="inline">{{ $order->shipping_name }}, {{ $order->shipping_country }}, {{ $order->shipping_zip }} {{ $order->shipping_city }}, {{ $order->shipping_address }}</dd>
            </div>
            <div>
                <dt class="inline font-medium">Számlázási cím: </dt>
                <dd class="inline">{{ $order->billing_name }}, {{ $order->billing_country }}, {{ $order->billing_zip }} {{ $order->billing_city }}, {{ $order->billing_address }}</dd>
            </div>
            <div><dt class="inline font-medium">Státusz: </dt><dd class="inline">{{ $order->orderStatus->name }}</dd></div>
        </dl>

        <a href="{{ route('catalog.index') }}" class="text-amber-600 hover:underline">Vissza a termékekhez</a>
    </div>
@endsection
