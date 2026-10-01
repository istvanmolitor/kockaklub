@extends('layouts.app')

@section('title', 'Szerverhiba történt')

@section('content')
    <x-error-page code="500" title="Hoppá, hiba történt"
                  message="Valami elromlott a szerverünkön. Csapatunk már dolgozik a megoldáson, kérjük próbáld újra később." />
@endsection
