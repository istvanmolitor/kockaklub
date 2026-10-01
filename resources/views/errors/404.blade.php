@extends('layouts.app')

@section('title', 'Az oldal nem található')

@section('content')
    <x-error-page code="404" title="Ez az oldal elgurult, mint egy kockacsúcs"
                  message="A keresett oldal nem található — lehet, hogy törölték, vagy megváltozott a címe." />
@endsection
