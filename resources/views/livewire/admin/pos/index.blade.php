<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.admin')] #[Title("Caisse - Wendy's Diner")] class extends Component
{
    // Product Selection
    public Collection $categories;
    public Collection $products;
    public ?int $selectedCategoryId = null;

    // Cart Management
    public array $cart = [];
    public float $total = 0.0;

    // --- Menu Flow Management ---
    public bool $showMenuModal = false;
    public string $menuStep = 'options';
    public ?Product $selectedBurger = null;

    // Properties to hold selectable items for the menu
    public Collection $availableSides;
    public Collection $availableSauces;
    public Collection $availableDrinks;

    // Properties to store the chosen items for the menu
    public ?int $selectedSideId = null;
    public ?int $selectedSauceId = null;
    public ?int $selectedDrinkId = null;

    // --- NEW: Note Management ---
    public string $itemNotes = '';

    // --- Payment Flow Management (CORRECTED) ---
    public bool $showPaymentModal = false;
    public array $payments = [];
    public string $newPaymentMethod = 'espèces';

    // LA SOLUTION : Les propriétés sont maintenant des chaînes de caractères, comme les inputs HTML.
    public string $newPaymentAmount = '';
    public string $cashReceived = '';

    // --- RÈGLES DE VALIDATION ---
    public function rules(): array
    {
        return [
            'newPaymentMethod' => 'required|in:espèces,carte',
            // 'numeric' garantit que la chaîne est un nombre valide.
            'newPaymentAmount' => 'required|numeric|min:0.1|max:' . $this->getRemainingAmount(),
            // Ajout d'une règle pour cashReceived pour la robustesse
            'cashReceived' => 'nullable|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->categories = Category::has('products')->orderBy('position')->get();
        $this->products = Product::orderBy('name')->get();
        $this->selectedCategoryId = $this->categories->first()?->id;

        // Pre-load sides and drinks for the menu modal
        $this->availableSides = Product::whereHas('category', fn($q) => $q->where('type', 'accompagnement'))->get();
        $this->availableSauces = Product::whereHas('category', fn($q) => $q->where('type', 'sauce'))->get();
        $this->availableDrinks = Product::whereHas('category', fn($q) => $q->where('type', 'boisson'))->get();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        if ($categoryId) {
            $this->products = Product::where('category_id', $categoryId)->orderBy('name')->get();
        } else {
            $this->products = Product::orderBy('name')->get();
        }
    }

    /**
     * This method is now a router. If the product is a burger, it opens the menu modal.
     * Otherwise, it adds it directly to the cart.
     */
    public function handleProductClick(Product $product): void
    {
        // Check if the product's category is of type 'burger'
        if ($product->category->type === 'burger') {
            $this->selectedBurger = $product;
            $this->showMenuModal = true;
        } else {
            $this->addToCart($product);
        }
    }

    /**
     * Add a simple product to the cart.
     */
    public function addToCart(Product $product, ?string $notes = null): void
    {
        // Simple products don't have unique notes, so we generate a simple ID
        $cartId = $product->id . ($notes ? '_' . crc32($notes) : '');

        if (isset($this->cart[$cartId])) {
            $this->cart[$cartId]['quantity']++;
        } else {
            $this->cart[$cartId] = [
                'id' => $cartId,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'is_menu' => false,
                'notes' => $notes,
            ];
        }
        $this->calculateTotal();
    }

    /**
     * Add the burger as a solo item to the cart.
     */
    public function addBurgerAsSolo(): void
    {
        $this->addToCart($this->selectedBurger, $this->itemNotes);
        $this->closeMenuModal();
    }

    /**
     * Add the complete menu to the cart.
     */
    public function addMenuToCart(): void
    {
        // Validation: ensure a side and a drink have been selected
        if (is_null($this->selectedSideId) || is_null($this->selectedSauceId) || is_null($this->selectedDrinkId)) {
            return;
        }

        $burger = $this->selectedBurger;
        $side = $this->availableSides->find($this->selectedSideId);
        $sauce = $this->availableSauces->find($this->selectedSauceId);
        $drink = $this->availableDrinks->find($this->selectedDrinkId);
        $menuId = 'menu_' . $burger->id . '_' . $side->id . '_' . $sauce->id . '_' . $drink->id . '_' . time();
        $menuPrice = $burger->price + config('wendys.pos.menu_surcharge');

        $this->cart[$menuId] = [
            'id' => $menuId,
            'name' => 'Menu ' . $burger->name,
            'price' => $menuPrice,
            'quantity' => 1,
            'is_menu' => true,
            'components' => [$burger->name, $side->name, $sauce->name, $drink->name],
            'notes' => $this->itemNotes
        ];

        $this->calculateTotal();
        $this->closeMenuModal();
    }

    /**
     * Close and reset the menu modal state.
     */
    public function closeMenuModal(): void
    {
        $this->showMenuModal = false;
        $this->menuStep = 'options'; // Reset to the first step
        $this->selectedBurger = null;
        $this->selectedSideId = null;
        $this->selectedSauceId = null;
        $this->selectedDrinkId = null;
        $this->itemNotes = '';
    }

    /**
     * Increment the quantity of a cart item.
     */
    public function incrementQuantity(string|int $cartId): void
    {
        if (isset($this->cart[$cartId])) {
            $this->cart[$cartId]['quantity']++;
            $this->calculateTotal();
        }
    }

    /**
     * Decrement the quantity of a cart item or remove it.
     */
    public function decrementQuantity(string|int $cartId): void
    {
        if (isset($this->cart[$cartId])) {
            if ($this->cart[$cartId]['quantity'] > 1) {
                $this->cart[$cartId]['quantity']--;
            } else {
                unset($this->cart[$cartId]);
            }
            $this->calculateTotal();
        }
    }

    /**
     * Clear the entire cart.
     */
    public function clearCart(): void
    {
        $this->cart = [];
        $this->calculateTotal();
    }

    /**
     * Calculate the total price of the cart.
     */
    private function calculateTotal(): void
    {
        $this->total = collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    /**
     * Open the payment modal and pre-fill the amount.
     */
    public function openPaymentModal(): void
    {
        // On convertit le float en string pour l'assigner à la propriété.
        $this->newPaymentAmount = (string) $this->getRemainingAmount();
        $this->cashReceived = '';
        $this->showPaymentModal = true;
    }

    /**
     * Add a payment line to the current order.
     */
    public function addPaymentLine(): void
    {
        $this->validate();

        $this->payments[] = [
            'method' => $this->newPaymentMethod,
            // On convertit la chaîne validée en float avant de la stocker.
            'amount' => (float) $this->newPaymentAmount,
        ];

        $remaining = $this->getRemainingAmount();
        $this->newPaymentAmount = $remaining > 0 ? (string) $remaining : '';
        $this->newPaymentMethod = 'carte';
    }

    /**
     * Remove a payment line.
     */
    public function removePaymentLine(int $index): void
    {
        if (isset($this->payments[$index])) {
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments); // Re-index array
        }
    }

    /**
     * Calculate the amount still due.
     */
    public function getRemainingAmount(): float
    {
        $paid = collect($this->payments)->sum('amount');
        return round($this->total - $paid, 2);
    }

    /**
     * Calculate change to be given back.
     */
    public function getChange(): float
    {
        // On s'assure de caster en float pour les calculs.
        $cashReceived = (float) $this->cashReceived;

        if ($cashReceived <= 0) {
            return 0.0;
        }

        $cashPayments = collect($this->payments)->where('method', 'espèces')->sum('amount');

        if ($cashReceived >= $cashPayments) {
            return round($cashReceived - $cashPayments, 2);
        }

        return 0.0;
    }

    /**
     * Save the entire order to the database.
     */
    public function saveOrder(): void
    {
        // Ensure the order is fully paid
        if ($this->getRemainingAmount() > 0) {
            return;
        }

        // Use a database transaction to ensure data integrity.
        // If anything fails, all changes will be rolled back.
        DB::transaction(function () {
            // 1. Create the Order
            $order = Order::create([
                'total_amount' => $this->total,
                'status' => 'terminée', // Or 'en cours' if you want a kitchen screen later
                'notes' => null, // We can add a general notes field later if needed
            ]);

            // 2. Create the OrderItems
            foreach ($this->cart as $item) {
                $order->items()->create([
                    // Find product_id even if it's a menu
                    'product_id' => is_numeric($item['id']) ? $item['id'] : Product::where('name', $item['components'][0])->first()->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'notes' => $item['notes'],
                ]);
            }

            // 3. Create the Payments
            foreach ($this->payments as $payment) {
                $order->payments()->create([
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                ]);
            }
        });

        // 4. Reset the POS for the next order
        $this->clearCart();
        $this->payments = [];
        $this->showPaymentModal = false;

        // Optional: Dispatch a success notification
        $this->dispatch('order-saved-successfully');
    }

}; ?>

{{-- We remove the default padding of the admin layout for this page --}}
<div class="-m-4 sm:-m-6 lg:-m-8">
    <div class="grid grid-cols-12 h-[calc(100vh-4rem)]">

        {{-- MAIN CONTENT: Product Selection (Left Side) --}}
        <div class="col-span-8 bg-zinc-50 dark:bg-zinc-900 p-6">
            <div class="flex flex-col h-full">

                {{-- Header --}}
                <header>
                    <h1 class="text-2xl text-primary-text font-bold">Nouvelle Commande</h1>
                </header>

                {{-- Categories & Products Area --}}
                <div class="mt-6 flex-grow overflow-y-auto">
                    {{-- Category Filters --}}
                    <div class="flex flex-wrap items-center gap-2 pb-4 border-b border-zinc-200 dark:border-zinc-700">
                        <button
                            wire:click="selectCategory(null)"
                            @class([
                                'px-4 py-2 text-sm font-semibold rounded-lg transition-colors',
                                'bg-accent-1 text-white' => !$selectedCategoryId,
                                'bg-white hover:bg-zinc-100 text-primary-text' => $selectedCategoryId,
                            ])
                        >
                            Tous
                        </button>
                        @foreach($categories as $category)
                            <button
                                wire:click="selectCategory({{ $category->id }})"
                                @class([
                                    'px-4 py-2 text-sm font-semibold rounded-lg transition-colors',
                                    'bg-accent-1 text-white' => $selectedCategoryId === $category->id,
                                    'bg-white hover:bg-zinc-100 text-primary-text' => $selectedCategoryId !== $category->id,
                                ])
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Products Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 pt-4">
                        @foreach($products as $product)
                            <button
                                wire:key="{{ $product->id }}"
                                wire:click="handleProductClick({{ $product->id }})"
                                class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-3 text-center transition-transform hover:scale-105"
                            >
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded-md mx-auto">
                                <p class="mt-2 text-sm font-bold text-primary-text truncate">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-500">{{ number_format($product->price, 2, ',', ' ') }} €</p>
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- SIDEBAR: Cart (Right Side) --}}
        <aside class="col-span-4 bg-white dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex flex-col h-full">

                {{-- Cart Header --}}
                <header class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-primary-text">Panier</h2>
                    @if(count($cart) > 0)
                        <flux:button
                            wire:click="clearCart"
                            variant="danger"
                            size="sm"
                            icon="x-circle"
                        >
                            Vider
                        </flux:button>
                    @endif
                </header>

                {{-- Cart Items Area --}}
                <div class="mt-6 flex-grow overflow-y-auto">
                    @forelse($cart as $item)
                        <div wire:key="{{ $item['id'] }}" class="flex items-center gap-4 py-3 border-b border-zinc-100 dark:border-zinc-700">
                            {{-- Quantity Controls --}}
                            <div class="flex items-center gap-2">
                                <button wire:click="decrementQuantity('{{ $item['id'] }}')" class="p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                    <flux:icon name="minus" class="size-5" />
                                </button>
                                <span class="font-bold w-6 text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="incrementQuantity('{{ $item['id'] }}')" class="p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                                    <flux:icon name="plus" class="size-5" />
                                </button>
                            </div>

                            {{-- Item Details --}}
                            <div class="flex-grow">
                                <p class="font-semibold text-primary-text">{{ $item['name'] }}</p>
                                @if($item['is_menu'])
                                    <ul class="text-xs text-zinc-500 list-disc list-inside">
                                        @foreach($item['components'] as $component)
                                            <li>{{ $component }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                {{-- Show notes if they exist --}}
                                @if(!empty($item['notes']))
                                    <p class="text-xs text-accent-1 italic pl-2 border-l-2 border-accent-1/50 mt-1">
                                        {{ $item['notes'] }}
                                    </p>
                                @endif
                            </div>

                            {{-- Item Price --}}
                            <p class="font-bold text-primary-text">
                                {{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} €
                            </p>
                        </div>
                    @empty
                        <div class="text-center text-zinc-400 py-16 h-full flex flex-col justify-center">
                            <flux:icon name="shopping-cart" class="size-12 mx-auto" />
                            <p class="mt-4">Le panier est vide.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Cart Footer --}}
                @if(count($cart) > 0)
                    <footer class="mt-6 border-t ... pt-6">
                        <div class="flex justify-between items-center text-lg font-bold">
                            <span>Total</span>
                            <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="mt-4">
                            {{-- This button now opens the payment modal --}}
                            <flux:button
                                wire:click="openPaymentModal"
                                variant="primary"
                                class="w-full !py-4 !text-lg"
                            >
                                Payer
                            </flux:button>
                        </div>
                    </footer>
                @endif

            </div>
        </aside>

        {{-- --- NEW: MENU MODAL --- --}}
        <flux:modal
            wire:model="showMenuModal"
            name="menu-modal"
            :title="$selectedBurger?->name"
            class="max-w-3xl" {{-- Agrandie pour le contenu --}}
            @close="closeMenuModal"
        >
            @if($selectedBurger)
                <div>
                    {{-- STEP 1: Options (Seul ou Menu) --}}
                    <div x-data="{}" x-show="$wire.menuStep === 'options'">
                        <div class="text-center">
                            <img src="{{ $selectedBurger->image_url }}" alt="{{ $selectedBurger->name }}" class="w-48 h-48 object-cover rounded-lg mx-auto mb-6">
                            {{-- Notes Field --}}
                            <div class="my-4 text-left">
                                <flux:input wire:model="itemNotes" placeholder="Instructions spéciales (ex: sans cornichons)..." />
                            </div>
                            <p class="text-lg text-primary-text/80 mb-6">Comment souhaitez-vous ajouter ce burger ?</p>
                            <div class="grid grid-cols-2 gap-4">
                                <button wire:click="addBurgerAsSolo" class="p-6 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                    <span class="text-lg font-bold">Seul</span>
                                    <span class="block text-sm text-zinc-500">{{ number_format($selectedBurger->price, 2, ',', ' ') }} €</span>
                                </button>
                                <button @click="$wire.menuStep = 'sides'" class="p-6 bg-accent-1 text-white rounded-lg hover:bg-accent-1/90 transition-colors">
                                    <span class="text-lg font-bold">En Menu</span>
                                    <span class="block text-sm opacity-80">+ {{ number_format(config('wendys.pos.menu_surcharge'), 2, ',', ' ') }} €</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: Choose Side --}}
                    <div x-data="{}" x-show="$wire.menuStep === 'sides'">
                        <h3 class="text-xl font-bold text-center mb-4">Choisissez un accompagnement</h3>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($availableSides as $side)
                                <button
                                    wire:click="$set('selectedSideId', {{ $side->id }})"
                                    @click="$wire.menuStep = 'sauces'"
                                    class="border rounded-lg p-3 text-center transition-all"
                                    :class="{ 'border-accent-1 ring-2 ring-accent-1': $wire.selectedSideId == {{ $side->id }} }"
                                >
                                    <img src="{{ $side->image_url }}" alt="{{ $side->name }}" class="w-full h-24 object-cover rounded-md mx-auto">
                                    <span class="block mt-2 text-sm font-bold">{{ $side->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- STEP 3: Choose Sauce (NEW) --}}
                    <div x-data="{}" x-show="$wire.menuStep === 'sauces'">
                        <h3 class="text-xl font-bold text-center mb-4">Choisissez une sauce</h3>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            @foreach($availableSauces as $sauce)
                                <button
                                    wire:click="$set('selectedSauceId', {{ $sauce->id }})"
                                    @click="$wire.menuStep = 'drinks'" {{-- Mène à l'étape des boissons --}}
                                    class="border rounded-lg p-3 text-center transition-all"
                                    :class="{ 'border-accent-1 ring-2 ring-accent-1': $wire.selectedSauceId == {{ $sauce->id }} }"
                                >
                                    {{-- Pas d'image pour les sauces, juste le nom --}}
                                    <span class="block text-sm font-bold">{{ $sauce->name }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex justify-start">
                            <button @click="$wire.menuStep = 'sides'" class="text-sm font-bold hover:underline">
                                &larr; Retour aux accompagnements
                            </button>
                        </div>
                    </div>

                    {{-- STEP 4: Choose Drink & Confirm --}}
                    <div x-data="{}" x-show="$wire.menuStep === 'drinks'">
                        <h3 class="text-xl font-bold text-center mb-4">Choisissez une boisson</h3>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            @foreach($availableDrinks as $drink)
                                <button
                                    wire:click="$set('selectedDrinkId', {{ $drink->id }})"
                                    class="border rounded-lg p-3 text-center transition-all"
                                    :class="{ 'border-accent-1 ring-2 ring-accent-1': $wire.selectedDrinkId == {{ $drink->id }} }"
                                >
                                    <img src="{{ $drink->image_url }}" alt="{{ $drink->name }}" class="w-full h-20 object-cover rounded-md mx-auto">
                                    <span class="block mt-2 text-xs font-bold">{{ $drink->name }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-center mt-6 pt-4 border-t">
                            <button @click="$wire.menuStep = 'sauces'" class="text-sm font-bold hover:underline">
                                &larr; Retour aux sauces
                            </button>
                            <flux:button
                                wire:click="addMenuToCart"
                                variant="primary"
                                x-data="{}"
                                x-bind:disabled="!$wire.selectedDrinkId"
                            >
                                Ajouter le Menu au Panier
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </flux:modal>

        {{-- --- NEW: PAYMENT MODAL --- --}}
        <flux:modal
            wire:model="showPaymentModal"
            name="payment-modal"
            title="Paiement de la Commande"
            class="max-w-xl"
        >
            {{-- Order Summary --}}
            <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                <div class="flex justify-between text-2xl font-bold text-primary-text">
                    <span>Total à Payer</span>
                    <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                </div>
                @if($this->getRemainingAmount() > 0)
                    <div class="flex justify-between text-lg font-bold text-accent-1 mt-2">
                        <span>Reste à Payer</span>
                        <span>{{ number_format($this->getRemainingAmount(), 2, ',', ' ') }} €</span>
                    </div>
                @else
                    <div class="flex justify-between text-lg font-bold text-lime-600 mt-2">
                        <span>Total Payé</span>
                        <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                    </div>
                @endif
            </div>

            {{-- Payment Lines --}}
            <div class="mt-4 space-y-2">
                @foreach($payments as $index => $payment)
                    <div class="flex items-center justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                        <span class="capitalize">{{ $payment['method'] }}</span>
                        <span class="font-semibold">{{ number_format($payment['amount'], 2, ',', ' ') }} €</span>
                        <button wire:click="removePaymentLine({{ $index }})">
                            <flux:icon name="x-mark" class="size-4 text-red-500" />
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Add Payment Form (only if there is an amount remaining) --}}
            @if($this->getRemainingAmount() > 0.009)
                <div wire:key="add-payment-form" class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Le sélecteur reste en .live, car c'est un seul événement --}}
                        <flux:field>
                            <flux:label for="payment_method">Moyen de paiement</flux:label>
                            <flux:select wire:model.live="newPaymentMethod" id="payment_method">
                                <option value="espèces">Espèces</option>
                                <option value="carte">Carte Bancaire</option>
                            </flux:select>
                        </flux:field>
                        {{-- CORRECTIF : On passe l'input du montant en .blur --}}
                        <flux:field>
                            <flux:label for="payment_amount">Montant</flux:label>
                            <flux:input wire:model.blur="newPaymentAmount" id="payment_amount" type="number" step="0.1" />
                        </flux:field>
                    </div>
                    <div class="text-right mt-2">
                        <flux:button wire:click="addPaymentLine" variant="primary" size="sm">Ajouter Paiement</flux:button>
                    </div>
                </div>
            @endif

            {{-- Cash Payment & Change Calculation --}}
            @if(collect($payments)->where('method', 'espèces')->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-dashed">
                    <flux:field>
                        <flux:label for="cash_received">Montant reçu en espèces</flux:label>
                        <flux:input wire:model.blur="cashReceived" id="cash_received" type="number" step="0.1" placeholder="Ex: 20.00" />
                    </flux:field>
                    @if($this->getChange() > 0)
                        <div class="mt-2 text-center text-xl font-bold p-3 bg-accent-2/20 text-accent-2 rounded-lg">
                            Monnaie à rendre : {{ number_format($this->getChange(), 2, ',', ' ') }} €
                        </div>
                    @endif
                </div>
            @endif

            {{-- Modal Footer --}}
            <div class="flex justify-end gap-2 pt-6">
                <flux:button type="button" variant="ghost" @click="$wire.showPaymentModal = false">Annuler</flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    class="!py-3"
                    wire:click="saveOrder"
                    :disabled="$this->getRemainingAmount() > 0"
                >
                    Enregistrer la Commande
                </flux:button>
            </div>
        </flux:modal>

    </div>
</div>


