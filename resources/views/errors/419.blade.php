@extends('layouts.app')

@section('title', 'Lejárt az oldal')

@section('content')
    <x-error-page code="419" title="Lejárt az oldal"
                  message="A munkameneted időközben lejárt. Frissítsd az oldalt, és próbáld újra." />
@endsection
