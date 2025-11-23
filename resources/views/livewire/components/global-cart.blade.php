<?php

use Livewire\Volt\Component;
use App\Services\CartService;

new class extends Component
{
    // Cart State
    public array $cart = [];
    public float $cartTotal = 0.0;
    public int $cartCount = 0;
    public bool $showCartModal = false;

    // Events
    protected $listeners = [
        'cart-updated' => 'refreshCart',
        'open-cart' => 'openCart'
    ];

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function refreshCart(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->get();
        $this->cartTotal = $cartService->total();
        $this->cartCount = $cartService->count();
    }

    public function openCart(): void
    {
        $this->showCartModal = true;
    }

    public function updateQuantity(string $itemId, int $newQuantity): void
    {
        app(CartService::class)->updateQuantity($itemId, $newQuantity);
        $this->refreshCart();
    }

    public function removeItem(string $itemId): void
    {
        app(CartService::class)->remove($itemId);
        $this->refreshCart();
    }

    public function goToCheckout()
    {
        $this->redirectRoute('checkout', navigate: true);
    }
}; ?>

<div>
    {{-- FLOATING CART BUTTON (Visible on all pages if cart not empty) --}}
    @if($cartCount > 0)
        <div class="fixed bottom-6 right-6 z-50 animate-bounce-in">
            <button 
                wire:click="$set('showCartModal', true)"
                class="bg-accent-1 hover:bg-accent-1/90 text-white shadow-xl rounded-full px-6 py-4 flex items-center gap-4 transition-all transform hover:scale-105"
            >
                <div class="relative">
                    <flux:icon name="shopping-cart" class="size-6" />
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                </div>
                <span class="font-bold text-lg">{{ number_format($cartTotal, 2, ',', ' ') }} €</span>
            </button>
        </div>
    @endif

    {{-- CART MODAL --}}
    <flux:modal wire:model="showCartModal" name="global-cart-modal" class="max-w-lg w-full bg-white dark:bg-zinc-900">
        <div class="p-6 h-[80vh] flex flex-col">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h2 class="text-2xl font-bold text-accent-1">Votre Panier</h2>
                <button wire:click="$set('showCartModal', false)" class="text-zinc-500 hover:text-zinc-800">
                    <flux:icon name="x-mark" class="size-6" />
                </button>
            </div>

            <div class="flex-grow overflow-y-auto space-y-4 pr-2">
                @forelse($cart as $item)
                    <div class="flex gap-4 items-start bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg">
                        <div class="flex flex-col items-center gap-1 bg-white dark:bg-zinc-700 rounded-lg p-1 shadow-sm">
                             <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})" class="p-1 hover:text-accent-1">
                                <flux:icon name="plus" class="size-4" />
                            </button>
                            <span class="font-bold text-sm">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})" class="p-1 hover:text-red-500">
                                <flux:icon name="minus" class="size-4" />
                            </button>
                        </div>
                        
                        <div class="flex-grow">
                            <h4 class="font-bold text-primary-text">{{ $item['name'] }}</h4>
                            @if($item['is_menu'])
                                <p class="text-xs text-zinc-500">{{ implode(', ', $item['components']) }}</p>
                                <p class="text-xs text-accent-1 mt-1">
                                    @php
                                        $basePrice = \App\Models\Product::find($item['product_id_for_db'])?->price ?? 0;
                                        $menuSurcharge = config('wendys.pos.menu_surcharge');
                                        $beerSurcharge = str_contains(implode(', ', $item['components']), '3 Monts') ? 2.00 : 0.00;
                                    @endphp
                                    (Base: {{ number_format($basePrice, 2, ',', ' ') }} € + Menu: {{ number_format($menuSurcharge, 2, ',', ' ') }} € 
                                    @if($beerSurcharge > 0) + Bière: {{ number_format($beerSurcharge, 2, ',', ' ') }} € @endif)
                                </p>
                            @endif
                            @if(!empty($item['notes']))
                                <p class="text-xs text-accent-1 italic">{{ $item['notes'] }}</p>
                            @endif
                        </div>
                        
                        <div class="text-right flex flex-col items-end">
                             <span class="font-bold">{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} €</span>
                             <button wire:click="removeItem('{{ $item['id'] }}')" class="text-xs text-red-500 hover:underline mt-1">Supprimer</button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-zinc-500">
                        <flux:icon name="shopping-bag" class="size-16 mx-auto mb-4 opacity-50" />
                        <p>Votre panier est vide.</p>
                        <button wire:click="$set('showCartModal', false)" class="mt-4 text-accent-1 font-bold hover:underline">Continuer vos achats</button>
                    </div>
                @endforelse
            </div>

            @if(count($cart) > 0)
                <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex justify-between items-center text-xl font-bold mb-6">
                        <span>Total</span>
                        <span class="text-accent-2">{{ number_format($cartTotal, 2, ',', ' ') }} €</span>
                    </div>
                    
                    {{-- Link to Checkout Component --}}
                    <flux:button wire:click="goToCheckout" class="w-full py-4 text-lg" variant="primary" icon="check">
                        Commander
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
