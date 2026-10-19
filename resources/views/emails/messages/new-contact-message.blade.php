<x-mail::message>
# Új üzenet érkezett a kapcsolatfelvételi űrlapon

**Név:** {{ $message->name }}<br>
**E-mail:** {{ $message->email }}<br>
@if($message->phone)
**Telefon:** {{ $message->phone }}<br>
@endif

Üzenet:

> {{ $message->message }}

<x-mail::button :url="$adminUrl">
Válasz az ügyfélnek
</x-mail::button>

Üdvözlettel,<br>
Kockaklub
</x-mail::message>
