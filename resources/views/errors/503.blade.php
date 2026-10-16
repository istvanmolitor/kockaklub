@extends('layouts.maintenance')

@section('title', 'Karbantartás alatt')

@section('content')
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-accent-600 via-accent2-600 to-accent3-500 px-6 py-16 text-center text-white sm:py-24">
        <div class="blob absolute -left-16 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="blob absolute -right-10 bottom-0 h-72 w-72 rounded-full bg-accent3-300/20" style="animation-delay: -5s"></div>
        <div class="blob absolute left-1/3 top-1/2 h-40 w-40 rounded-full bg-accent-300/15" style="animation-delay: -9s"></div>

        <div class="animate-rise relative mx-auto max-w-xl">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/15">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-black tracking-tight sm:text-3xl">Karbantartás alatt</h1>
            <p class="mt-3 text-base font-medium text-white/90 sm:text-lg">
                {{ $message ?? 'Hamarosan visszatérünk! Köszönjük a türelmed, amíg frissítjük az oldalt.' }}
            </p>
        </div>
    </div>
@endsection
