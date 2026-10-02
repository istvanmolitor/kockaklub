@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-8 md:flex-row">
        @auth
            <aside class="w-full shrink-0 md:w-56">
                <nav class="card space-y-1 p-2">
                    <a href="{{ route('account.show') }}"
                       class="block rounded-2xl px-4 py-2.5 text-sm font-bold transition {{ request()->routeIs('account.show') ? 'bg-gradient-to-r from-accent-600 to-accent3-500 text-white shadow-sm' : 'text-gray-600 hover:bg-accent-50 hover:text-accent-700' }}">
                        Profil
                    </a>
                    <a href="{{ route('account.orders') }}"
                       class="block rounded-2xl px-4 py-2.5 text-sm font-bold transition {{ request()->routeIs('account.orders') ? 'bg-gradient-to-r from-accent-600 to-accent3-500 text-white shadow-sm' : 'text-gray-600 hover:bg-accent-50 hover:text-accent-700' }}">
                        Megrendelés
                    </a>
                </nav>
            </aside>
        @endauth

        <div class="min-w-0 flex-1">
            @yield('account-content')
        </div>
    </div>
@endsection
