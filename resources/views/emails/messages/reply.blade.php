<x-mail::message>
@if ($message->initiated_by_admin)
# Üzenet a Kockaklubtól

{{ $message->reply }}
@else
# Válasz az üzenetedre

Köszönjük, hogy felvetted velünk a kapcsolatot! Az üzeneted:

> {{ $message->message }}

Válaszunk:

{{ $message->reply }}
@endif

Üdvözlettel,<br>
Kockaklub
</x-mail::message>
