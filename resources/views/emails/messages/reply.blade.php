<x-mail::message>
# Válasz az üzenetedre

Köszönjük, hogy felvetted velünk a kapcsolatot! Az üzeneted:

> {{ $message->message }}

Válaszunk:

{{ $message->reply }}

Üdvözlettel,<br>
Kockaklub
</x-mail::message>
