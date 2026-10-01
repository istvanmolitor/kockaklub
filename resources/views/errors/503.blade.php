@extends('layouts.app')

@section('title', 'Karbantartás alatt')

@section('content')
    <x-error-page code="503" title="Karbantartás alatt"
                  message="Hamarosan visszatérünk! Köszönjük a türelmed, amíg frissítjük az oldalt." />
@endsection
