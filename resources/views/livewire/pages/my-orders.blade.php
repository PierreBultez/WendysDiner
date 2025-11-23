<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;
use App\Models\Product;
use App\Livewire\Actions\Logout;

new #[Title('Mes Commandes')] 
#[Layout('components.layouts.app')] 
class extends Component {
    
    public bool $showRewardModal = false;
    public ?int $selectedTier = null;
    public array $rewardProducts = [];

    public function with(): array
    {
        return [
            'orders' => Auth::user()->orders()->latest()->get(),
            'loyaltyPoints' => Auth::user()->loyalty_points,
        ];
    }
    
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function openRewardModal(): void
    {
        $this->showRewardModal = true;
        $this->selectedTier = null;
        $this->rewardProducts = [];
    }

    public function selectTier(int $tier): void
    {
        $this->selectedTier = $tier;
        $this->rewardProducts = Product::where('loyalty_tier', $tier)
            ->where('is_available', true)
            ->get()
            ->toArray();
    }

    public function redeemProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_available || is_null($this->selectedTier)) return;

        // Determine points cost based on the selected tier
        $pointsCost = config('wendys.loyalty')[$this->selectedTier]['points'] ?? 0;

        // If it's a menu (burger), redirect to menu with redeem ID AND cost
        if ($product->category->type === 'burger') {
             $this->redirect(route('menu', ['redeem' => $product->id, 'points' => $pointsCost]), navigate: true);
             return;
        }

        // Simple product (Side, Drink, Dessert)
        app(CartService::class)->add([
            'id' => 'reward_' . $product->id . '_' . time(),
            'name' => $product->name . ' (Récompense)',
            'price' => 0,
            'quantity' => 1,
            'is_menu' => false,
            'is_reward' => true, 
            'points_cost' => $pointsCost, // Save the cost here
            'product_id_for_db' => $product->id,
        ]);

        $this->redirectRoute('menu', navigate: true);
    }
}; ?>

<div class="min-h-screen py-12 bg-zinc-50 dark:bg-zinc-900">
    <div class="container mx-auto px-4 max-w-5xl">
        
        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-primary-text">Mon Espace Client</h1>
                <p class="text-zinc-500 mt-1">Bienvenue, {{ Auth::user()->name }}</p>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('menu') }}" class="text-accent-1 hover:underline font-bold flex items-center gap-2">
                    <flux:icon name="plus-circle" class="size-5" />
                    Nouvelle Commande
                </a>
                
                <flux:button wire:click="logout" icon="arrow-right-start-on-rectangle" variant="subtle">
                    Se déconnecter
                </flux:button>
            </div>
        </div>

        {{-- Loyalty Card & Timeline --}}
        <div class="bg-gradient-to-br from-accent-1 to-accent-2 rounded-2xl p-8 text-white shadow-lg mb-10 relative overflow-hidden">
             <div class="relative z-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-2xl font-bold mb-1">Mon Programme Fidélité</h2>
                        <p class="opacity-90">Débloque des récompenses exclusives !</p>
                    </div>
                    <div class="text-right">
                        <div class="flex flex-col items-end gap-2">
                            <div class="flex items-end gap-2">
                                <span class="text-4xl font-black">{{ $loyaltyPoints }}</span>
                                <span class="text-lg font-bold mb-1">PTS</span>
                            </div>
                            @if($loyaltyPoints >= 30)
                                <flux:button wire:click="openRewardModal" variant="filled" class="bg-white text-accent-1 hover:bg-white/90 border-none shadow-md">
                                    <flux:icon name="gift" class="size-4 mr-2" />
                                    Dépenser mes points
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="relative pt-6 pb-2">
                    {{-- Progress Bar Background --}}
                    <div class="absolute top-1/2 left-0 w-full h-2 bg-black/20 rounded-full -translate-y-1/2"></div>
                    
                    {{-- Active Progress Bar --}}
                    @php
                        $maxPoints = 150;
                        // Piecewise scaling to match the 5-column layout (approx 20% width per step)
                        // 0pts -> 0%
                        // 30pts -> 10% (Center of col 1)
                        // 150pts -> 90% (Center of col 5)
                        
                        $percent = 0;
                        if ($loyaltyPoints <= 30) {
                            // 0 to 30 pts maps to 0% to 10%
                            $percent = ($loyaltyPoints / 30) * 10;
                        } else {
                            // 30 to 150 pts maps to 10% to 90%
                            // Input range: 120 (150-30)
                            // Output range: 80 (90-10)
                            $p = min($loyaltyPoints, 150);
                            $percent = 10 + ( ($p - 30) / 120 ) * 80;
                        }
                        
                        // Bonus: fill to 100% if > 150
                        if ($loyaltyPoints > 150) {
                             $percent = min(100, 90 + ($loyaltyPoints - 150));
                        }
                    @endphp
                    <div class="absolute top-1/2 left-0 h-2 bg-white rounded-full -translate-y-1/2 transition-all duration-1000 ease-out" style="width: {{ $percent }}%"></div>

                    {{-- Steps --}}
                    <div class="flex justify-between relative">
                        @foreach(config('wendys.loyalty') as $level => $tier)
                            @php
                                $reached = $loyaltyPoints >= $tier['points'];
                                $isNext = !$reached && ($loyaltyPoints < $tier['points']) && ($loyaltyPoints >= ($tiers[$level-1]['points'] ?? 0));
                            @endphp
                            <div class="flex flex-col items-center group w-1/5">
                                {{-- Dot --}}
                                <div class="w-8 h-8 rounded-full border-4 border-transparent flex items-center justify-center z-10 transition-all duration-300 
                                    {{ $reached ? 'bg-white text-accent-1 scale-110' : 'bg-black/40 text-white/50' }}
                                    {{ $isNext ? 'ring-4 ring-white/30 animate-pulse' : '' }}
                                ">
                                    @if($reached)
                                        <flux:icon name="check" class="size-4" />
                                    @else
                                        <span class="text-xs font-bold">{{ $level }}</span>
                                    @endif
                                </div>
                                
                                {{-- Tooltip/Label --}}
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-bold uppercase tracking-wider block {{ $reached ? 'text-white' : 'text-white/60' }}">
                                        {{ $tier['points'] }} pts
                                    </span>
                                    <span class="text-[10px] font-medium leading-tight block mt-1 {{ $reached ? 'text-white' : 'text-white/50' }}">
                                        {{ $tier['name'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
             </div>
             
             <!-- Decorative Circles -->
             <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
             <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
        </div>

        {{-- REWARD MODAL --}}
        <flux:modal wire:model="showRewardModal" name="reward-modal" class="max-w-2xl w-full">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-accent-1">Choisis ta récompense</h2>
                <p class="text-zinc-500">Fais-toi plaisir, c'est offert par la maison !</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                 @foreach(config('wendys.loyalty') as $level => $tier)
                    <button 
                        wire:click="selectTier({{ $level }})"
                        @disabled($loyaltyPoints < $tier['points'])
                        class="p-4 rounded-xl border-2 transition-all text-left group relative overflow-hidden
                            {{ $loyaltyPoints >= $tier['points'] 
                                ? ($selectedTier === $level ? 'border-accent-1 bg-accent-1/5' : 'border-zinc-200 hover:border-accent-1/50') 
                                : 'border-zinc-100 opacity-50 cursor-not-allowed bg-zinc-50' 
                            }}
                        "
                    >
                        <span class="text-xs font-bold text-zinc-400 uppercase mb-1 block">Niveau {{ $level }}</span>
                        <span class="font-bold text-primary-text block">{{ $tier['name'] }}</span>
                        <span class="text-xs text-accent-1 font-bold mt-2 block">{{ $tier['points'] }} points</span>
                        
                        @if($loyaltyPoints < $tier['points'])
                            <div class="absolute inset-0 bg-white/50 flex items-center justify-center backdrop-blur-[1px]">
                                <flux:icon name="lock-closed" class="size-5 text-zinc-400" />
                            </div>
                        @endif
                    </button>
                 @endforeach
            </div>

            @if($selectedTier)
                <div class="bg-zinc-50 rounded-xl p-6 animate-in fade-in slide-in-from-top-4">
                    <h3 class="font-bold text-lg mb-4">Produits disponibles :</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($rewardProducts as $product)
                            <button 
                                wire:click="redeemProduct({{ $product['id'] }})"
                                class="flex items-center gap-4 p-3 bg-white rounded-lg border border-zinc-200 hover:shadow-md transition-all text-left group"
                            >
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="w-16 h-16 object-cover rounded-md">
                                <div>
                                    <span class="font-bold text-sm block group-hover:text-accent-1 transition-colors">{{ $product['name'] }}</span>
                                    <span class="text-xs text-accent-2 font-bold">0,00 €</span>
                                    <span class="text-[10px] text-zinc-400 line-through block">{{ number_format($product['price'], 2) }} €</span>
                                </div>
                            </button>
                        @empty
                            <p class="col-span-2 text-center text-zinc-500 py-4">Aucun produit configuré pour ce niveau de récompense.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </flux:modal>

        {{-- Orders List --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-100 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-100 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-primary-text">Historique des commandes</h2>
            </div>

            @if($orders->isEmpty())
                <div class="p-12 text-center">
                    <div class="bg-zinc-50 dark:bg-zinc-900 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon name="shopping-bag" class="size-8 text-zinc-400" />
                    </div>
                    <h3 class="text-lg font-medium text-primary-text mb-2">Aucune commande pour le moment</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 mb-6">Vos commandes passées apparaîtront ici.</p>
                    <flux:button href="{{ route('menu') }}" variant="primary">Commander maintenant</flux:button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400 border-b border-zinc-100 dark:border-zinc-700">
                            <tr>
                                <th class="px-6 py-4 font-medium">N° Commande</th>
                                <th class="px-6 py-4 font-medium">Date</th>
                                <th class="px-6 py-4 font-medium">Statut</th>
                                <th class="px-6 py-4 font-medium">Total</th>
                                <th class="px-6 py-4 font-medium">Détails</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach($orders as $order)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-primary-text">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'en cours' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'terminée' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'annulée' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'à payer' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                            ];
                                            $colorClass = $statusColors[$order->status] ?? 'bg-zinc-100 text-zinc-700';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-primary-text">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
                                    <td class="px-6 py-4 text-zinc-500">
                                        <div class="text-xs">
                                            @foreach($order->items as $item)
                                                <div>{{ $item->quantity }}x {{ \App\Models\Product::find($item->product_id)?->name ?? 'Menu' }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
