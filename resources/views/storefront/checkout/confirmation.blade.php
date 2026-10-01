@extends('layouts.account')

@section('title', 'Rendelés visszaigazolás')

@section('account-content')
    <div class="card animate-pop">
        <h1 class="mb-2 text-2xl font-black text-gray-900">Köszönjük a rendelésed!</h1>
        <p class="mb-6 font-medium text-gray-600">Rendelésszám: <strong class="text-gray-900">{{ $order->order_number }}</strong></p>

        <ul class="mb-6 divide-y divide-gray-100 text-sm">
            @foreach ($order->items as $item)
                <li class="flex justify-between py-2 font-medium text-gray-700">
                    <span>{{ $item->product_name }} &times; {{ $item->quantity }}</span>
                    <span>{{ number_format($item->line_total, 0, ',', ' ') }} Ft</span>
                </li>
            @endforeach
        </ul>

        <dl class="mb-4 space-y-1 text-sm font-medium text-gray-600">
            <div class="flex justify-between"><dt>Részösszeg</dt><dd>{{ number_format($order->subtotal, 0, ',', ' ') }} Ft</dd></div>
            <div class="flex justify-between"><dt>Szállítás ({{ $order->shippingMethod->name }})</dt><dd>{{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft</dd></div>
            <div class="flex justify-between"><dt>Fizetési díj ({{ $order->paymentMethod->name }})</dt><dd>{{ number_format($order->payment_cost, 0, ',', ' ') }} Ft</dd></div>
        </dl>

        <div class="mb-6 flex justify-between text-lg font-black text-gray-900">
            <span>Végösszeg</span>
            <span>{{ number_format($order->total, 0, ',', ' ') }} Ft</span>
        </div>

        <dl class="mb-6 space-y-1 text-sm font-medium text-gray-600">
            <div>
                <dt class="inline font-bold text-gray-900">Szállítási cím: </dt>
                <dd class="inline">{{ $order->shipping_name }}, {{ $order->shipping_country }}, {{ $order->shipping_zip }} {{ $order->shipping_city }}, {{ $order->shipping_address }}</dd>
            </div>
            <div>
                <dt class="inline font-bold text-gray-900">Számlázási cím: </dt>
                <dd class="inline">{{ $order->billing_name }}, {{ $order->billing_country }}, {{ $order->billing_zip }} {{ $order->billing_city }}, {{ $order->billing_address }}</dd>
            </div>
            <div><dt class="inline font-bold text-gray-900">Státusz: </dt><dd class="inline">{{ $order->orderStatus->name }}</dd></div>
        </dl>

        <a href="{{ route('catalog.index') }}" class="font-bold text-accent-600 hover:underline">Vissza a termékekhez</a>
    </div>
@endsection
