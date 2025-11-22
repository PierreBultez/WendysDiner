<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Order;
use Carbon\Carbon;
use function Livewire\Volt\with;
use Livewire\Attributes\On;

new #[Layout('components.layouts.admin')] #[Title("Commandes - Wendy's Diner")] class extends Component
{

    public ?string $successMessage = null;
    public string $filterStatus = '';
    public string $filterPickupTime = '';
    public string $sortColumn = 'pickup_time';
    public string $sortDirection = 'asc';

    /**
     * Provide the list of today's orders to the view.
     * This listener will refresh the list when a payment is saved.
     */
    #[On('payment-saved')]
    public function with(): array
    {
        $allTodaysOrders = Order::whereDate('created_at', Carbon::today())->latest()->get();

        $ordersQuery = Order::whereDate('created_at', Carbon::today())
            ->with('items.product', 'payments')
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterPickupTime, fn ($query) => $query->whereTime('pickup_time', $this->filterPickupTime))
            ->orderBy($this->sortColumn, $this->sortDirection);

        return [
            'orders' => $ordersQuery->get(),
            'statuses' => $allTodaysOrders->pluck('status')->unique(),
            'pickupTimes' => $allTodaysOrders->pluck('pickup_time')->map(fn($time) => $time?->format('H:i'))->unique()->sort(),
        ];
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function completeOrder(Order $order): void
    {
        $order->update(['status' => 'terminée']);
        $this->successMessage = "La commande #{$order->id} a été marquée comme terminée.";
    }
}; ?>

<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl text-primary-text font-bold">Commandes du Jour</h1>
    </div>

    {{-- --- FILTERS SECTION --- --}}
    <div class="mb-4 mt-4 m grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:select wire:model.live="filterStatus" label="Filtrer par statut">
            <option value="">Tous les statuts</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterPickupTime" label="Filtrer par créneau">
            <option value="">Tous les créneaux</option>
            @foreach($pickupTimes as $time)
                @if($time)
                    <option value="{{ $time }}">{{ $time }}</option>
                @endif
            @endforeach
        </flux:select>
    </div>

    {{-- Orders Grid --}}
    <div class="mt-8">
        {{-- Grid Header --}}
        <div class="grid grid-cols-12 gap-x-4 px-6 py-3 text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-700 dark:text-zinc-400 font-bold rounded-t-lg">
            <div class="cursor-pointer" wire:click="sortBy('id')">
                <span class="flex items-center gap-1"># <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /></span>
            </div>
            <div class="col-span-2">Client</div> {{-- NEW COLUMN --}}
            <div class="col-span-4">Détails</div>
            <div class="cursor-pointer" wire:click="sortBy('pickup_time')">
                <span class="flex items-center gap-1">Créneau <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /></span>
            </div>
            <div>Paiement</div>
            <div class="cursor-pointer" wire:click="sortBy('total_amount')">
                <span class="flex items-center gap-1">Total <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /></span>
            </div>
            <div class="cursor-pointer" wire:click="sortBy('status')">
                <span class="flex items-center gap-1">Statut <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /></span>
            </div>
            <div>Actions</div>
        </div>

        {{-- Grid Body --}}
        <div class="space-y-2 mt-2">
            @forelse($orders as $order)
                <div wire:key="{{ $order->id }}" class="grid grid-cols-12 gap-x-4 items-center px-6 py-4 bg-white dark:bg-zinc-800 shadow rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm">
                    <div class="font-medium text-zinc-900 whitespace-nowrap dark:text-white">
                        {{ $order->id }}
                    </div>
                    {{-- CUSTOMER INFO --}}
                    <div class="col-span-2 text-xs">
                        @if($order->customer_name)
                            <p class="font-bold">{{ $order->customer_name }}</p>
                            @if($order->delivery_method === 'delivery')
                                <flux:badge size="sm" color="purple" icon="truck">Livraison</flux:badge>
                                <p class="text-zinc-500 mt-1">{{ $order->customer_address }}</p>
                            @else
                                <flux:badge size="sm" color="blue" icon="shopping-bag">Click & Collect</flux:badge>
                            @endif
                        @else
                            <span class="text-zinc-400 italic">Commande TPE/Caisse</span>
                        @endif
                    </div>
                    {{-- DETAILS --}}
                    <div class="col-span-4">
                        <ul class="text-base">
                            @foreach($order->items as $item)
                                <li>
                                    {{ $item->quantity }}x {{ $item->product?->name ?? 'Produit Inconnu' }}
                                    @if($item->notes)
                                        <span class="italic text-accent-1 font-bold text-xs">({{ $item->notes }})</span>
                                    @endif
                                    @if($item->components)
                                        <ul class="pl-4 list-disc list-inside text-zinc-500 text-xs">
                                            @foreach($item->components as $component)
                                                <li>{{ $component }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="font-semibold">
                        <flux:badge size="lg" color="cyan" variant="solid">{{ $order->pickup_time ? $order->pickup_time->format('H:i') : '-' }}</flux:badge>
                    </div>
                    <div>
                        @if($order->payment_method === 'revolut')
                            <flux:badge color="indigo">Revolut</flux:badge>
                        @elseif($order->payment_method === 'cash')
                            <flux:badge color="green">Espèces</flux:badge>
                        @elseif($order->payment_method === 'card_terminal')
                            <flux:badge color="zinc">CB TPE</flux:badge>
                        @else
                            @forelse($order->payments as $payment)
                                <flux:badge class="capitalize">{{ $payment->method }}</flux:badge>
                            @empty
                                <flux:badge color="red" variant="outline">Non payé</flux:badge>
                            @endforelse
                        @endif
                    </div>
                    <div class="font-bold">
                        {{ number_format($order->total_amount, 2, ',', ' ') }} €
                    </div>
                    <div>
                        @if($order->status === 'terminée')
                            <flux:badge color="lime" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                        @elseif($order->status === 'à payer')
                            <flux:badge color="red" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                        @else
                            <flux:badge color="amber" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                        @endif
                    </div>
                    <div>
                        @if($order->status === 'à payer')
                            <flux:button wire:click="$dispatch('show-payment-modal', { orderId: {{ $order->id }} })" size="sm" color="amber">Encaisser</flux:button>
                        @elseif($order->status === 'en cours')
                            <flux:button wire:click="completeOrder({{ $order->id }})" size="sm" color="lime">Terminer</flux:button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-12 px-6 py-12 text-center text-zinc-500 bg-white dark:bg-zinc-800 rounded-lg shadow">
                    Aucune commande enregistrée pour aujourd'hui.
                </div>
            @endforelse
        </div>
    </div>
    <livewire:admin.partials.payment-modal />
    
    @if ($successMessage)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => { $wire.set('successMessage', null) }, 3000)"
            x-show="show"
            class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white dark:bg-zinc-800 shadow-lg rounded-lg p-4"
        >
            <p class="text-green-600 font-bold">{{ $successMessage }}</p>
        </div>
    @endif
</div>