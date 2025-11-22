<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

new #[Title("La Carte - Wendy's Diner")] class extends Component
{
    // Product Selection
    public Collection $categories;
    
    #[Url(as: 'cat')]
    public ?int $selectedCategoryId = null;

    // Cart
    public array $cart = [];
    public float $cartTotal = 0.0;
    public int $cartCount = 0;

    // Menu Flow
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

    // Kids Menu Flow
    public bool $showKidsMenuModal = false;
    public string $kidsMenuStep = 'choice';
    public ?Product $selectedKidsProduct = null;
    public ?string $kidsMenuChoice = null;
    public ?string $kidsMenuSauce = null;
    public ?bool $kidsMenuCheddar = null;
    public string $kidsMenuItemNotes = '';

    // Cart Modal
    public bool $showCartModal = false;

    public function mount(): void
    {
        $this->categories = Category::has('products')
            ->with(['products' => fn ($query) => $query->orderBy('name')])
            ->orderBy('position')
            ->get();

        $this->availableSides = Product::whereHas('category', fn($q) => $q->where('type', 'accompagnement'))->get();
        $this->availableSauces = Product::whereHas('category', fn($q) => $q->where('type', 'sauce'))->get();
        $this->availableDrinks = Product::whereHas('category', fn($q) => $q->where('type', 'boisson'))->get();

        $this->refreshCart();
    }

    public function refreshCart(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->get();
        $this->cartTotal = $cartService->total();
        $this->cartCount = $cartService->count();
    }

    public function getFilteredProductsProperty()
    {
        if (!$this->selectedCategoryId) {
            return collect();
        }
        return $this->categories->find($this->selectedCategoryId)?->products ?? collect();
    }

    public function getSelectedCategoryNameProperty(): ?string
    {
        if (!$this->selectedCategoryId) {
            return null;
        }
        return $this->categories->find($this->selectedCategoryId)?->name;
    }

    // --- CART ACTIONS ---

    public function addToCart(int $productId, ?string $notes = null): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Generate simple ID for standalone items
        $cartId = $product->id . ($notes ? '_' . crc32($notes) : '');

        app(CartService::class)->add([
            'id' => $cartId,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'is_menu' => false,
            'notes' => $notes,
            'product_id_for_db' => $product->id,
        ]);

        $this->refreshCart();
        $this->dispatch('notify', message: 'Produit ajouté au panier !');
    }

    public function removeFromCart(string $cartId): void
    {
        app(CartService::class)->remove($cartId);
        $this->refreshCart();
    }

    public function updateQuantity(string $cartId, int $quantity): void
    {
        app(CartService::class)->updateQuantity($cartId, $quantity);
        $this->refreshCart();
    }

    // --- PRODUCT CLICK HANDLER ---

    public function handleProductClick(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        if ($product->category->type === 'burger') {
            $this->selectedBurger = $product;
            $this->showMenuModal = true;
        } elseif ($product->category->type === 'enfant') {
            $this->selectedKidsProduct = $product;
            $this->showKidsMenuModal = true;
        } else {
            $this->addToCart($product->id);
        }
    }

    // --- MENU FLOW ACTIONS ---

    public function addBurgerAsSolo(): void
    {
        $this->addToCart($this->selectedBurger->id, $this->itemNotes);
        $this->closeMenuModal();
    }

    public function addMenuToCart(): void
    {
        if (is_null($this->selectedSideId) || is_null($this->selectedSauceId) || is_null($this->selectedDrinkId)) {
            return;
        }

        $burger = $this->selectedBurger;
        $side = $this->availableSides->find($this->selectedSideId);
        
        if ($this->selectedSauceId === 0) {
            $sauceName = 'Sans Sauce';
        } else {
            $sauce = $this->availableSauces->find($this->selectedSauceId);
            $sauceName = $sauce->name;
        }
        
        $drink = $this->availableDrinks->find($this->selectedDrinkId);
        $menuId = 'menu_' . $burger->id . '_' . $side->id . '_' . $this->selectedSauceId . '_' . $drink->id . '_' . time();
        $menuPrice = $burger->price + config('wendys.pos.menu_surcharge');

        app(CartService::class)->add([
            'id' => $menuId,
            'name' => 'Menu ' . $burger->name,
            'price' => $menuPrice,
            'quantity' => 1,
            'is_menu' => true,
            'components' => [$burger->name, $side->name, $sauceName, $drink->name],
            'notes' => $this->itemNotes,
            'product_id_for_db' => $burger->id 
        ]);

        $this->refreshCart();
        $this->closeMenuModal();
        $this->dispatch('notify', message: 'Menu ajouté au panier !');
    }

    public function closeMenuModal(): void
    {
        $this->showMenuModal = false;
        $this->menuStep = 'options';
        $this->selectedBurger = null;
        $this->selectedSideId = null;
        $this->selectedSauceId = null;
        $this->selectedDrinkId = null;
        $this->itemNotes = '';
    }

    // --- KIDS MENU ACTIONS ---

    public function setKidsMenuChoice(string $choice): void
    {
        $this->kidsMenuChoice = $choice;
        $this->kidsMenuStep = 'sauce';
    }

    public function setKidsMenuSauce(string $sauce): void
    {
        $this->kidsMenuSauce = $sauce;
        if ($this->kidsMenuChoice === 'chunks') {
            $this->kidsMenuStep = 'notes';
        } else {
            $this->kidsMenuStep = 'cheddar';
        }
    }

    public function setKidsMenuCheddar(bool $hasCheddar): void
    {
        $this->kidsMenuCheddar = $hasCheddar;
        $this->kidsMenuStep = 'notes';
    }

    public function addKidsMenuToCart(): void
    {
        $name = $this->selectedKidsProduct->name . ' (' . ucfirst($this->kidsMenuChoice) . ')';
        $components = [
            ucfirst($this->kidsMenuChoice),
            'Frites',
            $this->kidsMenuSauce
        ];

        if ($this->kidsMenuChoice === 'burger' && $this->kidsMenuCheddar) {
            $components[] = 'Cheddar';
        }

        $cartId = 'kids_menu_' . $this->selectedKidsProduct->id . '_' . time();
        $product = $this->selectedKidsProduct;

        app(CartService::class)->add([
            'id' => $cartId,
            'name' => $name,
            'price' => $product->price,
            'quantity' => 1,
            'is_menu' => true,
            'components' => $components,
            'notes' => $this->kidsMenuItemNotes,
            'product_id_for_db' => $product->id
        ]);

        $this->refreshCart();
        $this->closeKidsMenuModal();
        $this->dispatch('notify', message: 'Menu enfant ajouté au panier !');
    }

    public function goToCheckout()
    {
        $this->redirectRoute('checkout', navigate: true);
    }

    public function closeKidsMenuModal(): void
    {
        $this->showKidsMenuModal = false;
        $this->kidsMenuStep = 'choice';
        $this->selectedKidsProduct = null;
        $this->kidsMenuChoice = null;
        $this->kidsMenuSauce = null;
        $this->kidsMenuCheddar = null;
        $this->kidsMenuItemNotes = '';
    }
}; ?>

<div class="bg-background min-h-screen pb-24">
    <!-- HERO SECTION -->
    <div class="relative h-64 md:h-96 flex items-center justify-center text-center bg-zinc-800 text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="/images/placeholders/diner-menu-board.jpg" alt="Menu board du Wendy's Diner" class="w-full h-full object-cover opacity-40">
        </div>
        <div class="relative z-10 p-4">
            <x-section-title
                tag="h1"
                title="Notre Carte"
                subtitle="Des classiques indémodables, préparés avec des produits frais."
                titleClasses="!text-white"
                subtitleClasses="!text-white/80 text-xl"
            />
        </div>
    </div>

    <!-- MENU CONTENT -->
    <div class="container mx-auto px-4 sm:px-10 lg:px-20 py-10 md:py-16">
        <!-- Category Filters -->
        <div class="sticky top-20 z-30 bg-background/95 backdrop-blur-sm py-4 mb-8 overflow-x-auto">
            <div class="flex flex-nowrap md:flex-wrap gap-2 px-2 md:justify-center min-w-max">
                {{-- "All" Button --}}
                <button
                    wire:click="$set('selectedCategoryId', null)"
                    @class([
                        'px-4 py-2 text-sm font-bold rounded-full transition-colors whitespace-nowrap',
                        'bg-accent-1 text-white' => !$selectedCategoryId,
                        'bg-white text-primary-text/70 hover:bg-zinc-100 border border-zinc-200' => $selectedCategoryId,
                    ])
                >
                    Toute la carte
                </button>
                {{-- Buttons for each category --}}
                @foreach($categories as $category)
                    <button
                        wire:click="$set('selectedCategoryId', {{ $category->id }})"
                        @class([
                            'px-4 py-2 text-sm font-bold rounded-full transition-colors whitespace-nowrap',
                            'bg-accent-1 text-white' => $selectedCategoryId === $category->id,
                            'bg-white text-primary-text/70 hover:bg-zinc-100 border border-zinc-200' => $selectedCategoryId !== $category->id,
                        ])
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- PRODUCTS DISPLAY -->
        @if($selectedCategoryId)
            <section>
                <h2 class="text-3xl font-bold text-accent-1 mb-8">{{ $this->selectedCategoryName }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($this->filteredProducts as $product)
                        <x-card class="overflow-hidden flex flex-col h-full group">
                            <div class="relative overflow-hidden h-56">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            </div>
                            <div class="p-5 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-accent-1">{{ $product->name }}</h3>
                                <p class="mt-2 text-primary-text/80 text-sm flex-grow">{{ $product->description }}</p>
                                <div class="mt-4 pt-4 border-t border-zinc-100 flex justify-between items-center">
                                    <span class="text-xl font-bold text-accent-2">{{ number_format($product->price, 2, ',', ' ') }} €</span>
                                    <button 
                                        wire:click="handleProductClick({{ $product->id }})" 
                                        class="bg-accent-1 hover:bg-accent-1/90 text-white p-2 rounded-full shadow-md transition-colors"
                                        title="Ajouter au panier"
                                    >
                                        <flux:icon name="plus" class="size-6" />
                                    </button>
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <p class="text-primary-text/60">Aucun produit ne correspond à votre sélection.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <div class="space-y-16">
                @foreach($categories as $category)
                    <section wire:key="category-{{ $category->id }}">
                        <h2 class="text-3xl font-bold text-accent-1 mb-8 sticky top-[4.5rem] z-20 bg-background/90 backdrop-blur py-2">{{ $category->name }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($category->products as $product)
                                <x-card class="overflow-hidden flex flex-col h-full group">
                                    <div class="relative overflow-hidden h-56">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                    </div>
                                    <div class="p-5 flex flex-col flex-grow">
                                        <h3 class="text-xl font-bold text-primary-text">{{ $product->name }}</h3>
                                        <p class="mt-2 text-primary-text/80 text-sm flex-grow">{{ $product->description }}</p>
                                        <div class="mt-4 pt-4 border-t border-zinc-100 flex justify-between items-center">
                                            <span class="text-xl font-bold text-accent-2">{{ number_format($product->price, 2, ',', ' ') }} €</span>
                                            <button 
                                                wire:click="handleProductClick({{ $product->id }})" 
                                                class="bg-accent-1 hover:bg-accent-1/90 text-white p-2 rounded-full shadow-md transition-colors"
                                                title="Ajouter au panier"
                                            >
                                                <flux:icon name="plus" class="size-6" />
                                            </button>
                                        </div>
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    <!-- FLOATING CART BUTTON -->
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

    <!-- MODALS -->
    
    {{-- CART MODAL --}}
    <flux:modal wire:model="showCartModal" name="cart-modal" class="max-w-lg w-full bg-white dark:bg-zinc-900">
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
                            @endif
                            @if(!empty($item['notes']))
                                <p class="text-xs text-accent-1 italic">{{ $item['notes'] }}</p>
                            @endif
                        </div>
                        
                        <div class="text-right">
                             <span class="font-bold block">{{ number_format($item['price'] * $item['quantity'], 2, ',', ' ') }} €</span>
                             <button wire:click="removeFromCart('{{ $item['id'] }}')" class="text-xs text-red-500 hover:underline mt-1">Supprimer</button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-zinc-500">
                        <flux:icon name="shopping-bag" class="size-16 mx-auto mb-4 opacity-50" />
                        <p>Votre panier est vide.</p>
                        <button wire:click="$set('showCartModal', false)" class="mt-4 text-accent-1 font-bold hover:underline">Retourner à la carte</button>
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

    {{-- MENU MODAL --}}
    <flux:modal
        wire:model="showMenuModal"
        name="menu-modal"
        :title="$selectedBurger?->name"
        class="max-w-3xl" 
        @close="closeMenuModal"
    >
        @if($selectedBurger)
            <div>
                {{-- STEP 1: Options (Seul ou Menu) --}}
                <div x-data="{}" x-show="$wire.menuStep === 'options'">
                    <div class="text-center">
                        <img src="{{ $selectedBurger->image_url }}" alt="{{ $selectedBurger->name }}" class="w-48 h-48 object-cover rounded-lg mx-auto mb-6">
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

                {{-- STEP 3: Choose Sauce --}}
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
                                @click="$wire.menuStep = 'drinks'"
                                class="border rounded-lg p-3 text-center transition-all"
                                :class="{ 'border-accent-1 ring-2 ring-accent-1': $wire.selectedSauceId == {{ $sauce->id }} }"
                            >
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

    {{-- KIDS MENU MODAL --}}
    <flux:modal
        wire:model="showKidsMenuModal"
        name="kids-menu-modal"
        :title="$selectedKidsProduct?->name"
        class="max-w-3xl"
        @close="closeKidsMenuModal"
    >
        @if($selectedKidsProduct)
            <div>
                {{-- STEP 1: Burger or Chunks? --}}
                <div x-data="{}" x-show="$wire.kidsMenuStep === 'choice'">
                    <h3 class="text-3xl text-accent-1 text-center mb-6">Burger ou Chunks ?</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="setKidsMenuChoice('burger')" class="p-6 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors text-center">
                            <span class="text-lg font-bold">Burger</span>
                        </button>
                        <button wire:click="setKidsMenuChoice('chunks')" class="p-6 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors text-center">
                            <span class="text-lg font-bold">Chunks</span>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Sauce --}}
                <div x-data="{}" x-show="$wire.kidsMenuStep === 'sauce'">
                    <h3 class="text-3xl text-accent-1 text-center mb-6">Quelle sauce ?</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <button wire:click="setKidsMenuSauce('Ketchup')" class="p-4 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors font-semibold">Ketchup</button>
                        <button wire:click="setKidsMenuSauce('Mayo')" class="p-4 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors font-semibold">Mayo</button>
                        <button wire:click="setKidsMenuSauce('Ketchup/Mayo')" class="p-4 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors font-semibold">Ketchup/Mayo</button>
                    </div>
                </div>

                {{-- STEP 3: Cheddar (only for burgers) --}}
                <div x-data="{}" x-show="$wire.kidsMenuStep === 'cheddar'">
                    <h3 class="text-3xl text-accent-1 text-center mb-6">Cheddar dans le burger ?</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="setKidsMenuCheddar(true)" class="p-6 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors font-semibold">Oui</button>
                        <button wire:click="setKidsMenuCheddar(false)" class="p-6 bg-zinc-100 dark:bg-zinc-700 rounded-lg hover:text-background hover:bg-accent-1 dark:hover:bg-zinc-600 transition-colors font-semibold">Non</button>
                    </div>
                </div>

                {{-- Final Step: Add notes and save --}}
                <div x-data="{}" x-show="$wire.kidsMenuStep === 'notes'">
                    <h3 class="text-3xl text-accent-1 text-center mb-6">Quelquechose à ajouter ?</h3>
                    <div class="mt-6 pt-4">
                        <flux:input wire:model="kidsMenuItemNotes" placeholder="Instructions spéciales..." />
                        <div class="flex justify-end mt-4">
                            <flux:button
                                wire:click="addKidsMenuToCart"
                                variant="primary"
                                x-data="{}"
                                x-show="$wire.kidsMenuChoice && $wire.kidsMenuSauce"
                            >
                                Ajouter au Panier
                            </flux:button>
                        </div>
                    </div>
            </div>
        @endif
    </flux:modal>

    {{-- NOTIFICATIONS --}}
    <div
        x-data="{ show: false, message: '' }"
        x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition.duration.300ms
        class="fixed top-24 right-4 z-[60] bg-accent-1 text-white px-6 py-3 rounded-lg shadow-lg font-bold"
        style="display: none;"
    >
        <span x-text="message"></span>
    </div>
</div>