@extends('layouts.app')

@section('title', 'Kockaklub')

@section('content')
    <div class="mb-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Üdvözlünk a Kockaklubban</h1>
        <p class="mt-2 text-gray-600">Fedezd fel legújabb termékeinket.</p>
        <a href="{{ route('catalog.index') }}" class="inline-block mt-4 rounded-md bg-amber-600 px-5 py-2.5 text-white font-medium hover:bg-amber-700">
            Termékek böngészése
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($products as $product)
            <x-storefront.product-card :product="$product" />
        @endforeach
    </div>
@endsection
