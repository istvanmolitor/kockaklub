@extends('layouts.app')

@section('title', 'Túl sok kérés')

@section('content')
    <x-error-page code="429" title="Egy kicsit lassabban!"
                  message="Túl sok kérést küldtél rövid idő alatt. Próbáld újra néhány másodperc múlva." />
@endsection
