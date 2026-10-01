<x-mail::message>
# Köszönjük a rendelésed!

Rendelésszám: **{{ $order->order_number }}**

<x-mail::table>
| Termék | Mennyiség | Egységár | Összesen |
| :----- | :-------: | -------: | -------: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 0, ',', ' ') }} Ft | {{ number_format($item->line_total, 0, ',', ' ') }} Ft |
@endforeach
</x-mail::table>

**Végösszeg: {{ number_format($order->total, 0, ',', ' ') }} Ft**

Szállítási cím: {{ $order->shipping_address }}

Szállítási mód: {{ $order->shippingMethod->name }}

Fizetési mód: {{ $order->paymentMethod->name }}

<x-mail::button :url="route('checkout.confirmation', $order)">
Rendelés megtekintése
</x-mail::button>

Köszönjük, hogy nálunk vásároltál!<br>
Kockaklub
</x-mail::message>
