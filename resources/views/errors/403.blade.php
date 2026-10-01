@extends('layouts.app')

@section('title', 'Hozzáférés megtagadva')

@section('content')
    <x-error-page code="403" title="Hozzáférés megtagadva"
                  message="Nincs jogosultságod megtekinteni ezt az oldalt." />
@endsection
