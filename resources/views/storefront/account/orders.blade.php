@extends('layouts.account')

@section('title', 'Rendeléseim')

@section('account-content')
    <h1 class="mb-6 text-2xl font-black text-gray-900">Rendeléseim</h1>

    @if ($orders->isEmpty())
        <p class="font-medium text-gray-600">Még nem adtál le rendelést.</p>
    @else
        <div class="card divide-y divide-gray-100 p-0">
            @foreach ($orders as $order)
                <a href="{{ route('checkout.confirmation', $order) }}" class="flex items-center justify-between p-4 transition hover:bg-accent-50/50">
                    <div>
                        <p class="font-bold text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-sm font-medium text-gray-500">{{ $order->created_at->format('Y.m.d.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} Ft</p>
                        <p class="text-sm font-medium text-gray-500">{{ $order->orderStatus->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
