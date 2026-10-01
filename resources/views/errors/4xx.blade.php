@extends('layouts.app')

@section('title', 'Hiba történt')

@section('content')
    <x-error-page :code="$exception->getStatusCode()" title="Hiba történt a kérésed feldolgozása közben"
                  message="Valami nem stimmel a kéréseddel. Ellenőrizd a megadott adatokat, és próbáld újra." />
@endsection
