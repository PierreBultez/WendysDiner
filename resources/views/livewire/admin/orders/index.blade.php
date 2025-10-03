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
            ->orderBy($this->sortColumn, $this->sortDirection); // <-- Apply sorting

        return [
            'orders' => $ordersQuery->get(),
            'statuses' => $allTodaysOrders->pluck('status')->unique(),
            'pickupTimes' => $allTodaysOrders->pluck('pickup_time')->map(fn($time) => $time?->format('H:i'))->unique()->sort(),
        ];
    }

    /**
     * --- NEW: Sorting Method ---
     */
    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Mark an order as 'completed'.
     * Using route-model binding for convenience and security.
     */
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

    {{-- --- NEW: FILTERS SECTION --- --}}
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

    {{-- Orders Table --}}
    <div class="mt-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-700 dark:text-zinc-400">
                <tr>
                    {{-- --- UPDATED: Sortable Headers --- --}}
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('id')">
                        <span class="flex items-center gap-1"># Commande @if($sortColumn === 'id') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif</span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('created_at')">
                        <span class="flex items-center gap-1">Heure @if($sortColumn === 'created_at') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif</span>
                    </th>
                    <th scope="col" class="px-6 py-3">Détails</th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('pickup_time')">
                        <span class="flex items-center gap-1">Créneau @if($sortColumn === 'pickup_time') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif</span>
                    </th>
                    <th scope="col" class="px-6 py-3">Paiement(s)</th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('total_amount')">
                        <span class="flex items-center gap-1">Total @if($sortColumn === 'total_amount') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif</span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('status')">
                        <span class="flex items-center gap-1">Statut @if($sortColumn === 'status') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif</span>
                    </th>
                    <th scope="col" class="relative px-6 py-3 w-1"><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr wire:key="{{ $order->id }}" class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-600">
                        <th scope="row" class="px-6 py-4 font-medium text-zinc-900 whitespace-nowrap dark:text-white">
                            {{ $order->id }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $order->created_at->format('H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <ul class="text-xs">
                                @foreach($order->items as $item)
                                    <li>
                                        {{ $item->quantity }}x {{ $item->product->name }}
                                        @if($item->notes)
                                            <span class="italic text-accent-1 font-bold">({{ $item->notes }})</span>
                                        @endif
                                        @if($item->components)
                                            <ul class="pl-4 list-disc list-inside text-zinc-600">
                                                @foreach($item->components as $component)
                                                    <li>{{ $component }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-6 py-4 font-semibold">
                            {{ $order->pickup_time ? $order->pickup_time->format('H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @forelse($order->payments as $payment)
                                <flux:badge class="capitalize">{{ $payment->method }}</flux:badge>
                            @empty
                                <flux:badge color="red" variant="outline">Aucun</flux:badge>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 font-bold">
                            {{ number_format($order->total_amount, 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4">
                            @if($order->status === 'terminée')
                                <flux:badge color="lime" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                            @elseif($order->status === 'à payer')
                                <flux:badge color="red" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                            @else
                                <flux:badge color="amber" variant="solid">{{ ucfirst($order->status) }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($order->status === 'à payer')
                                <flux:button wire:click="$dispatch('show-payment-modal', { orderId: {{ $order->id }} })" size="sm" color="amber">Encaisser</flux:button>
                            @elseif($order->status === 'en cours')
                                <flux:button wire:click="completeOrder({{ $order->id }})" size="sm" color="lime">Terminer</flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                            Aucune commande enregistrée pour aujourd'hui.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- On inclut la modale globale pour qu'elle puisse écouter les événements --}}
    <livewire:admin.partials.payment-modal />

    {{-- --- TOAST --- --}}
    @if ($successMessage)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => { $wire.set('successMessage', null) }, 3000)"
            x-show="show"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white dark:bg-zinc-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <flux:icon name="check-circle" class="size-6 text-green-500" />
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-primary-text dark:text-white">
                            Succès !
                        </p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $successMessage }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="$wire.set('successMessage', null)" class="inline-flex text-zinc-400 hover:text-zinc-500">
                            <span class="sr-only">Fermer</span>
                            <flux:icon name="x-mark" class="size-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
