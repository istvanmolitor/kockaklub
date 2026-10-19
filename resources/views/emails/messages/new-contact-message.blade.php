<x-mail::message>
# Új üzenet érkezett a kapcsolatfelvételi űrlapon

**Név:** {{ $message->name }}<br>
**E-mail:** {{ $message->email }}<br>
@if($message->phone)
**Telefon:** {{ $message->phone }}<br>
@endif

Üzenet:

> {{ $message->message }}

Üdvözlettel,<br>
Kockaklub
</x-mail::message>
