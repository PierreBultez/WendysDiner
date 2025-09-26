<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

new #[Title("La Carte - Wendy's Diner")] class extends Component
{
    public Collection $categories;

    #[Url(as: 'cat')]
    public ?int $selectedCategoryId = null;

    public function mount(): void
    {
        // Now we get all categories with their products preloaded.
        $this->categories = Category::has('products')
            ->with(['products' => fn ($query) => $query->orderBy('name')])
            ->orderBy('position')
            ->get();
    }

    /**
     * Get the filtered list of products WHEN a category is selected.
     */
    public function getFilteredProductsProperty()
    {
        if (!$this->selectedCategoryId) {
            return collect();
        }
        return $this->categories->find($this->selectedCategoryId)?->products ?? collect();
    }

    // New computed property to get the name of the selected category.
    public function getSelectedCategoryNameProperty(): ?string
    {
        if (!$this->selectedCategoryId) {
            return null;
        }
        return $this->categories->find($this->selectedCategoryId)?->name;
    }
}; ?>

<div class="bg-background">
    <!-- HERO SECTION -->
    <div class="relative h-96 flex items-center justify-center text-center bg-zinc-800 text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="/images/placeholders/diner-menu-board.jpg" alt="Menu board du Wendy's Diner" class="w-full h-full object-cover opacity-40">
        </div>
        <div class="relative z-10 p-4">
            <x-section-title
                tag="h1"
                title="Notre Carte"
                subtitle="Des classiques indémodables, préparés avec des produits frais."
                titleClasses="!text-white"
                subtitleClasses="!text-white/80"
            />
        </div>
    </div>

    <!-- MENU CONTENT -->
    <div class="container mx-auto px-4 sm:px-10 lg:px-20 py-16 md:py-24">
        <!-- Category Filters -->
        <div class="flex flex-wrap justify-center gap-2 md:gap-4 mb-12">
            {{-- "All" Button --}}
            <button
                wire:click="$set('selectedCategoryId', null)"
                @class([
                    'px-4 py-2 text-sm font-bold rounded-full transition-colors',
                    'bg-accent-1 text-white' => !$selectedCategoryId,
                    'bg-white text-primary-text/70 hover:bg-zinc-100' => $selectedCategoryId,
                ])
            >
                Toute la carte
            </button>
            {{-- Buttons for each category --}}
            @foreach($categories as $category)
                <button
                    wire:click="$set('selectedCategoryId', {{ $category->id }})"
                    @class([
                        'px-4 py-2 text-sm font-bold rounded-full transition-colors',
                        'bg-accent-1 text-white' => $selectedCategoryId === $category->id,
                        'bg-white text-primary-text/70 hover:bg-zinc-100' => $selectedCategoryId !== $category->id,
                    ])
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <!-- =============================================================== -->
        <!-- DISPLAY LOGIC (COMPLETELY REWORKED)                             -->
        <!-- =============================================================== -->

        {{-- CASE 1: A category IS selected --}}
        @if($selectedCategoryId)
            <section>
                {{-- CORRECTIF : Affiche le titre de la catégorie sélectionnée --}}
                <h2 class="text-3xl md:text-4xl font-bold text-accent-1 mb-12">{{ $this->selectedCategoryName }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @forelse($this->filteredProducts as $product)
                        <x-card class="overflow-hidden">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-2xl font-bold text-accent-1">{{ $product->name }}</h3>
                                <p class="mt-2 text-primary-text/80 text-sm">{{ $product->description }}</p>
                                <div class="mt-auto pt-4">
                                    <div class="text-xl font-bold text-accent-2">
                                        {{ number_format($product->price, 2, ',', ' ') }} €
                                    </div>
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
        {{-- CASE 2: NO category is selected ("Toute la carte") --}}
        @else
            <div class="space-y-12">
                @foreach($categories as $category)
                    <section wire:key="category-{{ $category->id }}">
                        {{-- Category Title --}}
                        <h2 class="text-3xl md:text-4xl font-bold text-accent-1 mb-12">{{ $category->name }}</h2>

                        {{-- Products Grid for this category --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            @foreach($category->products as $product)
                                <x-card class="overflow-hidden">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">
                                    <div class="p-6 flex flex-col flex-grow">
                                        <h3 class="text-2xl font-bold text-primary-text">{{ $product->name }}</h3>
                                        <p class="mt-2 text-primary-text/80 text-sm">{{ $product->description }}</p>
                                        <div class="mt-auto pt-4">
                                            <div class="text-xl font-bold text-accent-2">
                                                {{ number_format($product->price, 2, ',', ' ') }} €
                                            </div>
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
</div>
