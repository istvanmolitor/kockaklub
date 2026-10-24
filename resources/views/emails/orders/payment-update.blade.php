<x-mail::message>
# Fizetési értesítés

Rendelésszám: **{{ $order->order_number }}**

@if ($payment)
Beérkezett egy **{{ number_format($payment->amount, 0, ',', ' ') }} Ft** összegű fizetés {{ $payment->received_at->format('Y.m.d. H:i') }} időpontban.

@endif
@if ($previousStatus)
Új fizetési státusz: **{{ $order->payment_status->label() }}**

@endif
<x-mail::button :url="route('checkout.confirmation', $order)">
Rendelés megtekintése
</x-mail::button>

Köszönjük, hogy nálunk vásároltál!<br>
Kockaklub
</x-mail::message>
