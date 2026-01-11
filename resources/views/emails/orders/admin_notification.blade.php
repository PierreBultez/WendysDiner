<x-mail::message>
# Nouvelle commande #{{ $order->id }}

Une nouvelle commande vient d'arriver !

## Client
**Nom :** {{ $order->customer_name }}  
**Email :** {{ $order->customer_email }}  
**Téléphone :** {{ $order->customer_phone }}  

## Commande
**Heure de retrait :** {{ $order->pickup_time->format('d/m/Y à H:i') }}  
**Type :** {{ $order->delivery_method === 'delivery' ? 'LIVRAISON' : 'A EMPORTER' }}  
@if($order->delivery_method === 'delivery')
**Adresse :** {{ $order->customer_address }}
@endif
**Paiement :** {{ ucfirst($order->payment_method) }} ({{ $order->status }})

<x-mail::table>
| Article | Qté | Prix | Notes |
| :--- | :---: | :---: | :--- |
@foreach($order->items as $item)
| **{{ $item->product ? $item->product->name : 'Menu' }}** @if($item->components)<br><small>{{ implode(', ', $item->components) }}</small>@endif | {{ $item->quantity }} | {{ number_format($item->unit_price * $item->quantity, 2) }} € | {{ $item->notes }} |
@endforeach
| **Total** | | **{{ number_format($order->total_amount, 2) }} €** | |
</x-mail::table>

<x-mail::button :url="route('dashboard.orders.index')">
Gérer la commande
</x-mail::button>

</x-mail::message>