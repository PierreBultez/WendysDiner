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
use Carbon\Carbon;

new #[Layout('components.layouts.admin')] #[Title("Caisse - Wendy's Diner")] class extends Component
{
    // Product Selection
    public Collection $categories;
    public Collection $products;
    public ?int $selectedCategoryId = null;
    public ?string $pickupTime = null;

    // Cart Management
    public array $cart = [];
    public float $total = 0.0;

    // Flux du menu
    public bool $showMenuModal = false;
    public string $menuStep = 'options';
    public ?Product $selectedBurger = null;
    public Collection $availableSides;
    public Collection $availableSauces;
    public Collection $availableDrinks;
    public ?int $selectedSideId = null;
    public ?int $selectedSauceId = null;
    public ?int $selectedDrinkId = null;
    public string $itemNotes = '';

    // --- Toast Notification Management ---
    public ?string $successMessage = null;

    public function mount(): void
    {
        $this->categories = Category::has('products')->orderBy('position')->get();
        $this->products = Product::orderBy('name')->get();
        $this->selectedCategoryId = $this->categories->first()?->id;
        $this->pickupTime = Carbon::now()->format('H:i');
        $this->availableSides = Product::whereHas('category', fn($q) => $q->where('type', 'accompagnement'))->get();
        $this->availableSauces = Product::whereHas('category', fn($q) => $q->where('type', 'sauce'))->get();
        $this->availableDrinks = Product::whereHas('category', fn($q) => $q->where('type', 'boisson'))->get();
    }

    // This is the main action to save an order.
    // It can be paid immediately or later.
    public function saveOrder(bool $payLater = false): void
    {
        if (empty($this->cart)) return;

        $order = DB::transaction(function () use ($payLater) {
            $order = Order::create([
                'total_amount' => $this->total,
                'status' => $payLater ? 'à payer' : 'en cours',
                'pickup_time' => Carbon::parse($this->pickupTime)->toDateTimeString(),
            ]);

            foreach ($this->cart as $item) {
                $productId = null;
                if (is_numeric($item['id'])) {
                    $productId = $item['id'];
                } elseif ($item['is_menu']) {
                    $burgerName = str_replace('Menu ', '', $item['name']);
                    $product = Product::where('name', $burgerName)->first();
                    $productId = $product?->id;
                }
                if ($productId) {
                    $order->items()->create([
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'notes' => $item['notes'],
                        'components' => $item['is_menu'] ? $item['components'] : null,
                    ]);
                }
            }
            return $order;
        });

        // Si le paiement est immédiat, on ouvre la modale de paiement externe.
        if (!$payLater) {
            $this->dispatch('show-payment-modal', orderId: $order->id);
        }

        $this->resetPos();
        $this->successMessage = 'Commande enregistrée avec succès !';
    }

    // Réinitialise l'interface de caisse.
    public function resetPos(): void
    {
        $this->cart = [];
        $this->total = 0.0;
        $this->pickupTime = Carbon::now()->format('H:i');
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
        // On gère le cas spécial "Sans Sauce"
        if ($this->selectedSauceId === 0) {
            $sauceName = 'Sans Sauce';
        } else {
            $sauce = $this->availableSauces->find($this->selectedSauceId);
            $sauceName = $sauce->name;
        }
        $drink = $this->availableDrinks->find($this->selectedDrinkId);
        $menuId = 'menu_' . $burger->id . '_' . $side->id . '_' . $this->selectedSauceId . '_' . $drink->id . '_' . time();
        $menuPrice = $burger->price + config('wendys.pos.menu_surcharge');

        $this->cart[$menuId] = [
            'id' => $menuId,
            'name' => 'Menu ' . $burger->name,
            'price' => $menuPrice,
            'quantity' => 1,
            'is_menu' => true,
            'components' => [$burger->name, $side->name, $sauceName, $drink->name],
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

}; ?>

{{-- We remove the default padding of the admin layout for this page --}}
<div class="-m-4 sm:-m-6 lg:-m-8">
    <div class="relative w-full h-full">
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
                        <footer class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-6">
                            <div class="mb-4">
                                <flux:input type="time" wire:model="pickupTime" label="Heure de retrait" />
                            </div>
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span>Total</span>
                                <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                            </div>
                            <div class="mt-4 flex gap-2">
                                {{-- Bouton pour sauvegarder sans paiement immédiat --}}
                                <flux:button wire:click="saveOrder(true)" variant="subtle" class="w-full !py-3">
                                    Payer plus tard
                                </flux:button>
                                {{-- Bouton pour sauvegarder et ouvrir la modale de paiement --}}
                                <flux:button wire:click="saveOrder(false)" variant="primary" class="w-full !py-3">
                                    Payer
                                </flux:button>
                            </div>
                        </footer>
                    @endif
                </div>
            </aside>
        </div>

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
                            <button
                                wire:click="$set('selectedSauceId', 0)"
                                @click="$wire.menuStep = 'drinks'"
                                class="bg-accent-2 text-background border rounded-lg p-3 text-center transition-all flex items-center justify-center min-h-[6rem]"
                                :class="{ 'border-accent-1 ring-2 ring-accent-1': $wire.selectedSauceId === 0 }"
                            >
                                <span class="block text-sm font-bold">Sans Sauce</span>
                            </button>
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
    </div>

    {{-- --- NOTRE TOAST PERSONNALISÉ --- --}}
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
    <livewire:admin.partials.payment-modal />
</div>



