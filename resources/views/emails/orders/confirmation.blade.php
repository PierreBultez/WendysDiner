<x-mail::message>
# Merci pour votre commande, {{ $order->customer_name }} !

Votre commande **#{{ $order->id }}** a bien été reçue et est en cours de préparation.

## Détails de la commande

**Date de retrait :** {{ $order->pickup_time->format('d/m/Y à H:i') }}  
**Mode de retrait :** {{ $order->delivery_method === 'delivery' ? 'Livraison' : 'Click & Collect' }}  
@if($order->delivery_method === 'delivery')
**Adresse :** {{ $order->customer_address }}
@endif

<x-mail::table>
| Article | Qté | Prix |
| :--- | :---: | :---: |
@foreach($order->items as $item)
| **{{ $item->product ? $item->product->name : 'Menu' }}** @if($item->components)<br><small>{{ implode(', ', $item->components) }}</small>@endif | {{ $item->quantity }} | {{ number_format($item->unit_price * $item->quantity, 2) }} € |
@endforeach
| **Total** | | **{{ number_format($order->total_amount, 2) }} €** |
</x-mail::table>

**Moyen de paiement :** {{ ucfirst($order->payment_method) }}

<x-mail::button :url="route('my-orders')">
Voir ma commande
</x-mail::button>

Merci de votre confiance,<br>
L'équipe de {{ config('app.name') }}
</x-mail::message>