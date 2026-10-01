@extends('layouts.account')

@section('title', 'Rendeléseim')

@section('account-content')
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Rendeléseim</h1>

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
