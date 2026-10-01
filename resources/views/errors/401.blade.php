@extends('layouts.app')

@section('title', 'Bejelentkezés szükséges')

@section('content')
    <x-error-page code="401" title="Bejelentkezés szükséges"
                  message="Ehhez az oldalhoz be kell jelentkezned." />
@endsection
