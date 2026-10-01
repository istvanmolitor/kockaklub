@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-8 md:flex-row">
        <aside class="w-full shrink-0 md:w-56">
            <nav class="space-y-1 rounded-lg border border-gray-200 bg-white p-2">
                <a href="{{ route('account.show') }}"
                   class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('account.show') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    Profil
                </a>
                <a href="{{ route('account.orders') }}"
                   class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('account.orders') ? 'bg-amber-50 text-amber-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    Megrendelés
                </a>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            @yield('account-content')
        </div>
    </div>
@endsection
