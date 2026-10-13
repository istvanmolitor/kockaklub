@extends('layouts.account')

@section('title', 'Rendelés visszaigazolás')
@section('robots', 'noindex,nofollow')

@section('account-content')
    <div class="card animate-pop overflow-hidden p-0">
        <div class="relative overflow-hidden bg-gradient-to-br from-accent-600 via-accent2-600 to-accent3-500 px-6 py-8 text-white sm:px-10 sm:py-10">
            <div class="blob absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="blob absolute -bottom-12 -left-8 h-32 w-32 rounded-full bg-white/10" style="animation-delay: 4s"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black">Köszönjük a rendelésed!</h1>
                        <p class="mt-0.5 font-medium text-white/80">Rendelésszám: <strong class="font-black text-white">{{ $order->order_number }}</strong></p>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-2 sm:items-end">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-bold text-white shadow-sm backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        {{ $order->orderStatus->name }}
                    </span>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-bold shadow-sm backdrop-blur-sm {{ $order->isPaid() ? 'bg-green-500/90 text-white' : 'bg-amber-400/90 text-amber-950' }}">
                        <span class="h-2 w-2 rounded-full {{ $order->isPaid() ? 'bg-white' : 'bg-amber-950' }}"></span>
                        {{ $order->payment_status->label() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-10">
            <h2 class="mb-4 text-sm font-bold tracking-wide text-gray-400 uppercase">Tételek</h2>
            <ul class="mb-8 divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <li class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
                        <img src="{{ $item->product->default_image_url }}" alt="{{ $item->product_name }}"
                             class="h-16 w-16 shrink-0 rounded-2xl border-2 border-gray-100 object-cover">

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-sm font-medium text-gray-500">{{ $item->quantity }} db &times; {{ number_format($item->unit_price, 0, ',', ' ') }} Ft</p>
                        </div>

                        <span class="shrink-0 font-black text-gray-900">{{ number_format($item->line_total, 0, ',', ' ') }} Ft</span>
                    </li>
                @endforeach
            </ul>

            <div class="rounded-2xl bg-gray-50 p-5">
                <dl class="space-y-1.5 text-sm font-medium text-gray-600">
                    <div class="flex justify-between"><dt>Részösszeg</dt><dd>{{ number_format($order->subtotal, 0, ',', ' ') }} Ft</dd></div>
                    <div class="flex justify-between"><dt>Szállítás ({{ $order->shippingMethod->name }})</dt><dd>{{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft</dd></div>
                    <div class="flex justify-between"><dt>Fizetési díj ({{ $order->paymentMethod->name }})</dt><dd>{{ number_format($order->payment_cost, 0, ',', ' ') }} Ft</dd></div>
                </dl>

                <div class="mt-4 flex items-center justify-between border-t-2 border-gray-200 pt-4">
                    <span class="text-lg font-black text-gray-900">Végösszeg</span>
                    <span class="text-xl font-black text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} Ft</span>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border-2 border-gray-100 p-5">
                    <div class="mb-2 flex items-center gap-2 text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-accent-600">
                            <path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M3 16v5h5"/><path d="M21 16v5h-5"/>
                            <path d="M3 3l7 7"/><path d="M14 14l7 7"/>
                        </svg>
                        <h3 class="font-bold">Szállítási cím</h3>
                    </div>
                    <p class="text-sm font-medium text-gray-600">
                        {{ $order->shipping_name }}<br>
                        {{ $order->shipping_zip }} {{ $order->shipping_city }}, {{ $order->shipping_address }}<br>
                        {{ $order->shippingCountry?->name }}
                    </p>
                </div>

                <div class="rounded-2xl border-2 border-gray-100 p-5">
                    <div class="mb-2 flex items-center gap-2 text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-accent-600">
                            <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 14h4"/>
                        </svg>
                        <h3 class="font-bold">Számlázási cím</h3>
                    </div>
                    <p class="text-sm font-medium text-gray-600">
                        {{ $order->billing_name }}<br>
                        {{ $order->billing_zip }} {{ $order->billing_city }}, {{ $order->billing_address }}<br>
                        {{ $order->billingCountry?->name }}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border-2 border-gray-100 p-5">
                <div class="mb-2 flex items-center gap-2 text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-accent-600">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
                    </svg>
                    <h3 class="font-bold">Fizetési információk</h3>
                </div>

                <dl class="space-y-1 text-sm font-medium text-gray-600">
                    <div class="flex justify-between gap-4"><dt>Fizetési mód</dt><dd class="text-right">{{ $order->paymentMethod->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt>Fizetési státusz</dt><dd class="text-right">{{ $order->payment_status->label() }}</dd></div>
                </dl>

                @if ($order->paymentMethod->description)
                    <p class="mt-2 text-sm font-medium text-gray-500">{{ $order->paymentMethod->description }}</p>
                @endif

                @unless ($order->isPaid())
                    @if ($order->paymentMethod->type === \App\Enums\PaymentMethodType::PayPal)
                        <a href="{{ route('payment.paypal.pay', $order) }}" class="btn-primary btn-sm mt-4">
                            Fizetés PayPal-lal
                        </a>
                    @elseif ($order->paymentMethod->type === \App\Enums\PaymentMethodType::BankTransfer)
                        <div class="mt-4 rounded-2xl border-2 border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                            <p class="mb-2 font-bold">Kérjük, utald el az összeget az alábbi bankszámlára:</p>
                            <dl class="space-y-1">
                                @if (setting('company_name'))
                                    <div class="flex justify-between gap-4"><dt>Kedvezményezett</dt><dd class="text-right">{{ setting('company_name') }}</dd></div>
                                @endif
                                @if (setting('szamlazz_bank_name'))
                                    <div class="flex justify-between gap-4"><dt>Bank</dt><dd class="text-right">{{ setting('szamlazz_bank_name') }}</dd></div>
                                @endif
                                @if (setting('szamlazz_bank_account_number'))
                                    <div class="flex justify-between gap-4"><dt>Számlaszám</dt><dd class="text-right">{{ setting('szamlazz_bank_account_number') }}</dd></div>
                                @endif
                                <div class="flex justify-between gap-4"><dt>Közlemény</dt><dd class="text-right">{{ $order->order_number }}</dd></div>
                                <div class="flex justify-between gap-4"><dt>Összeg</dt><dd class="text-right">{{ number_format($order->total, 0, ',', ' ') }} Ft</dd></div>
                            </dl>
                        </div>
                    @endif
                @endunless
            </div>

            <a href="{{ route('catalog.index') }}" class="btn-secondary btn-sm mt-8">
                Vissza a termékekhez
            </a>
        </div>
    </div>
@endsection
