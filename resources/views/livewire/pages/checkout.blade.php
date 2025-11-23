<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Services\CartService;
use App\Services\RevolutService;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

new #[Title("Validation - Wendy's Diner")] class extends Component {
    
    // Cart Data
    public array $cart = [];
    public float $total = 0.0;
    
    // Guest Form
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_phone = '';
    public string $customer_address = '';
    
    // Options
    public string $deliveryMethod = 'pickup'; // pickup, delivery
    public string $paymentMethod = 'revolut'; // DEFAULT: revolut
    
    // Scheduling
    public string $selectedSlot = '';
    public array $slots = [];
    
    // UI State
    public bool $isProcessing = false;
    
    // Revolut
    public ?string $revolutToken = null;
    public ?int $currentOrderId = null;
    public string $revolutMode = 'sandbox';

    public function mount(): void
    {
        $this->revolutMode = config('services.revolut.mode', 'sandbox');
        $this->refreshCart();
        if (empty($this->cart)) {
            $this->redirectRoute('menu');
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->customer_name = $user->name;
            $this->customer_email = $user->email;
        }

        $this->generateSlots();
    }

    public function refreshCart(): void
    {
        $service = app(CartService::class);
        $this->cart = $service->get();
        $this->total = $service->total();
        
        // Add delivery surcharge if applicable
        if ($this->deliveryMethod === 'delivery') {
            $this->total += 2.00;
        }
    }

    public function updatedDeliveryMethod(): void
    {
        $this->refreshCart();
        // Also need to re-validate or re-generate slots if delivery rules differ? No, logic seems same for now.
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
        
        if (empty($this->cart)) {
            $this->redirectRoute('menu');
        }
    }

    public function generateSlots(): void
    {
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek; // 0=Sunday, 5=Friday, 6=Saturday

        // Define Opening Hours per Day (Start, End)
        // Note: End time is the CLOSING time. Last slot must be 15min before.
        $schedules = [
            Carbon::FRIDAY => [
                ['11:30', '13:30'],
                ['18:30', '21:30'],
            ],
            Carbon::SATURDAY => [
                ['18:30', '21:30'],
            ],
            Carbon::SUNDAY => [
                ['18:30', '21:00'],
            ],
        ];

        // If today is not a working day, empty slots (or handle next day logic if requested later)
        if (!isset($schedules[$dayOfWeek])) {
            $this->slots = [];
            return;
        }

        $todaysSchedule = $schedules[$dayOfWeek];
        $allSlots = [];
        
        // Rule: First available slot must be at least 30 mins from now
        $minTime = Carbon::now()->addMinutes(30);

        foreach ($todaysSchedule as $shift) {
            $shiftStart = Carbon::parse($shift[0]);
            $shiftEnd = Carbon::parse($shift[1]);
            
            // Rule: Last slot is Closing Time - 15 minutes
            $lastSlotLimit = $shiftEnd->copy()->subMinutes(15);

            $currentSlot = $shiftStart->copy();

            while ($currentSlot->lte($lastSlotLimit)) {
                
                // Rule: Slot must be in the future AND after the prep time (30mn)
                if ($currentSlot->gt(Carbon::now()) && $currentSlot->gte($minTime)) {
                    $allSlots[] = $currentSlot->format('H:i');
                }
                
                $currentSlot->addMinutes(15);
            }
        }

        // Check DB for taken slots (limit 1 order per slot)
        $takenSlots = Order::whereDate('pickup_time', Carbon::today())
            ->whereIn('status', ['en cours', 'à payer', 'terminée']) // Filter valid orders only
            ->get()
            ->map(function ($order) {
                return $order->pickup_time->format('H:i');
            })
            ->toArray();

        $this->slots = array_map(function($time) use ($takenSlots) {
            return [
                'time' => $time,
                'available' => !in_array($time, $takenSlots)
            ];
        }, $allSlots);
    }

    public function processOrder(RevolutService $revolutService): void
    {
        $this->validate([
            'customer_name' => 'required|min:3',
            'customer_email' => 'required|email',
            'customer_phone' => 'required',
            'customer_address' => $this->deliveryMethod === 'delivery' ? 'required' : 'nullable',
            'selectedSlot' => 'required',
            'paymentMethod' => 'required',
        ], [
            'customer_name.required' => 'Votre nom est requis.',
            'customer_email.required' => 'Votre email est requis pour la confirmation.',
            'customer_phone.required' => 'Votre téléphone est requis.',
            'customer_address.required' => 'L\'adresse est requise pour la livraison.',
            'selectedSlot.required' => 'Veuillez choisir un créneau.',
        ]);

        $this->isProcessing = true;

        // Final Check for Slot Availability
        $pickupTime = Carbon::createFromFormat('H:i', $this->selectedSlot);
        $pickupDateTime = Carbon::today()->setTimeFrom($pickupTime);

        // Check if slot is taken by another valid order
        if (Order::where('pickup_time', $pickupDateTime)->whereIn('status', ['en cours', 'à payer', 'terminée'])->exists()) {
            $this->addError('selectedSlot', 'Ce créneau vient d\'être réservé. Veuillez en choisir un autre.');
            $this->generateSlots(); 
            $this->isProcessing = false;
            return;
        }

        // Variable to hold the created order ID for deletion in catch block
        $createdOrderId = null;

        try {
            // Determine initial status
            // If Revolut, it's 'à payer' until confirmed.
            // If others, it's 'en cours' (immediately confirmed for kitchen).
            $initialStatus = $this->paymentMethod === 'revolut' ? 'à payer' : 'en cours';

            $order = DB::transaction(function () use ($pickupDateTime, $initialStatus) {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'total_amount' => $this->total,
                    'status' => $initialStatus,
                    'pickup_time' => $pickupDateTime,
                    'customer_name' => $this->customer_name,
                    'customer_email' => $this->customer_email,
                    'customer_phone' => $this->customer_phone,
                    'customer_address' => $this->deliveryMethod === 'delivery' ? $this->customer_address : null,
                    'delivery_method' => $this->deliveryMethod,
                    'payment_method' => $this->paymentMethod,
                ]);

                foreach ($this->cart as $item) {
                     $order->items()->create([
                        'product_id' => $item['product_id_for_db'] ?? ($item['is_menu'] ? null : $item['id']),
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'points_cost' => $item['points_cost'] ?? 0, // Save points cost
                        'notes' => $item['notes'] ?? null,
                        'components' => $item['is_menu'] ? ($item['components'] ?? null) : null,
                    ]);
                }
                return $order;
            });

            $createdOrderId = $order->id;

            // --- PAYMENT HANDLING ---

            if ($this->paymentMethod === 'revolut') {
                // Call Revolut API
                $revolutResponse = $revolutService->createOrder(
                    amount: $this->total,
                    currency: 'EUR', 
                    description: "Commande #{$order->id} - Wendy's Diner"
                );

                $this->revolutToken = $revolutResponse['token'];
                $this->currentOrderId = $order->id;
                $this->isProcessing = false; 
                // Return here. The view will detect $revolutToken and trigger the JS.
                return;
            }

            // If not Revolut, finalize immediately
            $this->awardAndDeductLoyaltyPoints($order);
            app(CartService::class)->clear();
            $this->redirectRoute('success');

        } catch (
Exception $e) {
            // CLEANUP: If order was created but API failed, delete it to free up the slot
            if ($createdOrderId) {
                Order::where('id', $createdOrderId)->delete();
            }
            
            $this->addError('base', 'Une erreur est survenue: ' . $e->getMessage());
            $this->isProcessing = false;
        }
    }

    /**
     * Called by Frontend JS when Revolut payment is successful
     */
    public function handleRevolutPaymentSuccess(): void
    {
        if ($this->currentOrderId) {
            $order = Order::find($this->currentOrderId);
            if ($order) {
                $order->update(['status' => 'en cours']);
                // Log payment
                $order->payments()->create([
                    'amount' => $order->total_amount,
                    'method' => 'revolut'
                ]);
                
                $this->awardAndDeductLoyaltyPoints($order);
            }
        }
        
        app(CartService::class)->clear();
        $this->redirectRoute('success');
    }

    private function awardAndDeductLoyaltyPoints(Order $order): void
    {
        if (!$order->user_id) return;

        $user = \App\Models\User::find($order->user_id);
        if (!$user) return;

        // 1. Award Points for paid amount
        $pointsEarned = (int) $order->total_amount;
        if ($pointsEarned > 0) {
            $user->increment('loyalty_points', $pointsEarned);
        }

        // 2. Deduct Points for rewards based on stored cost
        $pointsToDeduct = $order->items()->sum('points_cost');
        
        if ($pointsToDeduct > 0) {
            $user->decrement('loyalty_points', $pointsToDeduct);
        }
    }
};
?>

<div class="bg-zinc-50 dark:bg-zinc-900 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <div class="mb-8">
            <a href="{{ route('menu') }}" class="text-accent-1 hover:underline font-bold flex items-center gap-2">
                <flux:icon name="arrow-left" class="size-4" />
                Retour à la carte
            </a>
            <h1 class="text-3xl font-bold text-primary-text mt-4">Validation de la commande</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: FORM --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- 1. Coordonnées --}}
                <section class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
                    <h2 class="text-xl font-bold text-accent-1 mb-6 border-b pb-2">1. Vos Coordonnées</h2>
                    <div class="space-y-4">
                        <flux:input wire:model="customer_name" label="Nom complet" placeholder="ex: Pierre Dupont" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:input wire:model="customer_email" label="Email (Requis)" type="email" />
                            <flux:input wire:model="customer_phone" label="Téléphone" type="tel" placeholder="06 12 34 56 78" />
                        </div>
                    </div>
                </section>

                {{-- 2. Mode de retrait --}}
                <section class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
                    <h2 class="text-xl font-bold text-accent-1 mb-6 border-b pb-2">2. Mode de retrait</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            wire:click="$set('deliveryMethod', 'pickup')"
                            @class([
                                'p-4 rounded-lg border-2 transition-all flex flex-col items-center gap-2',
                                'border-accent-1 bg-accent-1/5' => $deliveryMethod === 'pickup',
                                'border-zinc-200 hover:border-zinc-300' => $deliveryMethod !== 'pickup',
                            ])
                        >
                            <flux:icon name="shopping-bag" class="size-8" class="{{ $deliveryMethod === 'pickup' ? 'text-accent-1' : 'text-zinc-400' }}" />
                            <span @class(['font-bold', 'text-accent-1' => $deliveryMethod === 'pickup', 'text-zinc-600' => $deliveryMethod !== 'pickup'])>Click & Collect</span>
                        </button>
                        <button 
                            wire:click="$set('deliveryMethod', 'delivery')"
                            @class([
                                'p-4 rounded-lg border-2 transition-all flex flex-col items-center gap-2',
                                'border-accent-1 bg-accent-1/5' => $deliveryMethod === 'delivery',
                                'border-zinc-200 hover:border-zinc-300' => $deliveryMethod !== 'delivery',
                            ])
                        >
                            <flux:icon name="truck" class="size-8" class="{{ $deliveryMethod === 'delivery' ? 'text-accent-1' : 'text-zinc-400' }}" />
                            <span @class(['font-bold', 'text-accent-1' => $deliveryMethod === 'delivery', 'text-zinc-600' => $deliveryMethod !== 'delivery'])>Livraison (+2,00 €)</span>
                        </button>
                    </div>

                    @if($deliveryMethod === 'delivery')
                        <div class="mt-6 animate-in fade-in slide-in-from-top-2">
                            <flux:input wire:model="customer_address" label="Adresse de livraison" placeholder="Numéro, Rue, Ville..." />
                        </div>
                    @endif
                </section>

                {{-- 3. Créneau Horaire --}}
                <section class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center mb-6 border-b pb-2">
                        <h2 class="text-xl font-bold text-accent-1">3. Heure de réception</h2>
                        <button wire:click="generateSlots" class="text-sm text-zinc-500 hover:text-accent-1">Actualiser</button>
                    </div>
                    
                    @error('selectedSlot') <p class="text-red-500 text-sm mb-4">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                        @foreach($slots as $slot)
                            <button
                                wire:click="$set('selectedSlot', '{{ $slot['time'] }}')"
                                @disabled(!$slot['available'])
                                @class([
                                    'px-2 py-2 rounded text-sm font-semibold transition-colors disabled:opacity-30 disabled:cursor-not-allowed',
                                    'bg-accent-1 text-white shadow-md' => $selectedSlot === $slot['time'],
                                    'bg-zinc-100 hover:bg-zinc-200 text-zinc-700' => $selectedSlot !== $slot['time']
                                ])
                            >
                                {{ $slot['time'] }}
                            </button>
                        @endforeach
                    </div>
                    @if(empty($slots))
                         <div class="text-center py-4 bg-zinc-50 rounded-lg border border-zinc-200 mt-4">
                             <p class="text-zinc-500 font-semibold">Aucun créneau disponible pour aujourd'hui.</p>
                             <p class="text-xs text-zinc-400 mt-1">Nos horaires : Ven 11h30-13h30 / 18h30-21h30, Sam 18h30-21h30, Dim 18h30-21h00.</p>
                         </div>
                    @endif
                </section>

                {{-- 4. Paiement --}}
                <section class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-sm">
                    <h2 class="text-xl font-bold text-accent-1 mb-6 border-b pb-2">4. Paiement</h2>
                    <div class="space-y-3">
                         <!-- Revolut Option -->
                         <label 
                            class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors"
                            :class="$wire.paymentMethod === 'revolut' ? 'border-accent-1 ring-1 ring-accent-1' : 'border-zinc-200'"
                         >
                            <input type="radio" wire:model.live="paymentMethod" value="revolut" class="sr-only">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="$wire.paymentMethod === 'revolut' ? 'border-accent-1' : 'border-zinc-300'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-accent-1" x-show="$wire.paymentMethod === 'revolut'"></div>
                                </div>
                                <div>
                                    <span class="font-bold block">Carte Bancaire (en ligne)</span>
                                    <span class="text-xs text-zinc-500">Paiement sécurisé immédiat</span>
                                </div>
                            </div>
                        </label>

                        <!-- Card Terminal Option -->
                        <label 
                            class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors"
                            :class="$wire.paymentMethod === 'card_terminal' ? 'border-accent-1 ring-1 ring-accent-1' : 'border-zinc-200'"
                        >
                            <input type="radio" wire:model.live="paymentMethod" value="card_terminal" class="sr-only">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="$wire.paymentMethod === 'card_terminal' ? 'border-accent-1' : 'border-zinc-300'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-accent-1" x-show="$wire.paymentMethod === 'card_terminal'"></div>
                                </div>
                                <div>
                                    <span class="font-bold block">Carte Bancaire (Sur place / Livraison)</span>
                                    <span class="text-xs text-zinc-500">Paiement via TPE au moment du retrait</span>
                                </div>
                            </div>
                        </label>

                         <!-- Cash Option -->
                         <label 
                            class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors"
                            :class="$wire.paymentMethod === 'cash' ? 'border-accent-1 ring-1 ring-accent-1' : 'border-zinc-200'"
                        >
                            <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="$wire.paymentMethod === 'cash' ? 'border-accent-1' : 'border-zinc-300'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-accent-1" x-show="$wire.paymentMethod === 'cash'"></div>
                                </div>
                                <div>
                                    <span class="font-bold block">Espèces (Sur place / Livraison)</span>
                                    <span class="text-xs text-zinc-500">Au moment du retrait</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </section>

            </div>

            {{-- RIGHT COLUMN: SUMMARY --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-lg sticky top-6">
                    <h2 class="text-xl font-bold text-primary-text mb-6">Récapitulatif</h2>
                    
                    <div class="space-y-4 mb-6 max-h-64 overflow-y-auto pr-2">
                        @foreach($cart as $item)
                            <div class="flex justify-between text-sm items-start gap-2 border-b border-zinc-100 pb-4 last:border-0">
                                {{-- Quantity Controls --}}
                                <div class="flex flex-col items-center gap-1 bg-zinc-100 dark:bg-zinc-700 rounded-lg p-1">
                                    <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})" class="p-0.5 hover:text-accent-1 transition-colors">
                                        <flux:icon name="plus" class="size-3" />
                                    </button>
                                    <span class="font-bold text-xs">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})" class="p-0.5 hover:text-red-500 transition-colors">
                                        <flux:icon name="minus" class="size-3" />
                                    </button>
                                </div>

                                <div class="flex-grow">
                                    <span class="font-bold block">{{ $item['name'] }}</span>
                                    @if($item['is_menu'])
                                        <p class="text-xs text-zinc-500">{{ implode(', ', $item['components']) }}</p>
                                        <p class="text-xs text-accent-1 mt-1">
                                            @php
                                                $basePrice = \App\Models\Product::find($item['product_id_for_db'])?->price ?? 0;
                                                $menuSurcharge = config('wendys.pos.menu_surcharge');
                                                $hasBeer = str_contains(implode(', ', $item['components']), '3 Monts');
                                                $beerSurcharge = $hasBeer ? 2.00 : 0.00;
                                            @endphp
                                            (Base: {{ number_format($basePrice, 2, ',', ' ') }} € + Menu: {{ number_format($menuSurcharge, 2, ',', ' ') }} €
                                            @if($hasBeer) + Bière: {{ number_format($beerSurcharge, 2, ',', ' ') }} € @endif)
                                        </p>
                                    @endif
                                    @if(!empty($item['notes']))
                                        <p class="text-xs text-accent-1 italic">{{ $item['notes'] }}</p>
                                    @endif
                                </div>
                                
                                <div class="text-right flex flex-col items-end">
                                    <span class="font-bold">{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} €</span>
                                    <button wire:click="removeItem('{{ $item['id'] }}')" class="text-xs text-red-500 hover:underline mt-1 flex items-center gap-1">
                                        <flux:icon name="trash" class="size-3" />
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4 mb-6 space-y-2">
                        @if($deliveryMethod === 'delivery')
                            <div class="flex justify-between items-center text-sm text-zinc-600">
                                <span>Frais de livraison</span>
                                <span>+ 2,00 €</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-xl font-bold text-accent-1 pt-2 border-t border-zinc-100">
                            <span>Total à payer</span>
                            <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                        </div>
                    </div>

                    <flux:button wire:click="processOrder" variant="primary" class="w-full py-4" icon="check-circle" :disabled="$isProcessing">
                        Confirmer la commande
                    </flux:button>

                    @if($errors->any())
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-red-600 text-sm font-bold">Veuillez corriger les erreurs :</p>
                            <ul class="list-disc list-inside text-red-600 text-xs mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

        {{-- REVOLUT JS INTEGRATION --}}
        <script>
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                
                // DYNAMIC SCRIPT LOADING BASED ON MODE
                // We check a global variable or data attribute set by Blade
                // Since we are in Blade, we can inject the URL directly.
                var isSandbox = @json(config('services.revolut.mode') === 'sandbox');
                var scriptUrl = isSandbox 
                    ? "https://sandbox-merchant.revolut.com/embed.js" 
                    : "https://merchant.revolut.com/embed.js";

                js.src = scriptUrl; 
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'revolut-checkout'));
        </script>
        
        <div 
            x-data="{
                initRevolut() {
                    let token = $wire.revolutToken;
                    if (token) {
                        this.openPopup(token);
                    }
                },
                openPopup(token) {
                    // Ensure the global variable is loaded
                    if (typeof RevolutCheckout === 'undefined') {
                        console.error('RevolutCheckout not loaded yet. Retrying...');
                        setTimeout(() => this.openPopup(token), 500);
                        return;
                    }
    
                    // Determine mode: 'sandbox' or nothing (for production)
                    let mode = $wire.revolutMode === 'sandbox' ? 'sandbox' : null;
                    
                    console.log('🚀 Initializing RevolutCheckout:', { token, mode, wireMode: $wire.revolutMode });

                    RevolutCheckout(token, mode).then(function(instance) {
                        instance.payWithPopup({
                            name: $wire.customer_name,
                            email: $wire.customer_email,
                            phone: $wire.customer_phone,
                            onSuccess() {
                                $wire.handleRevolutPaymentSuccess();
                            },
                            onError(error) {
                                alert('Erreur de paiement: ' + error);
                            },
                            onCancel() {
                                alert('Paiement annulé.');
                            }
                        });
                    });
                }
            }"
            x-effect="initRevolut()"
        ></div>
</div>
