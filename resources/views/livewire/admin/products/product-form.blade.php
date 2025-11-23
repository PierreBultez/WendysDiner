<?php

use App\Livewire\Forms\Admin\ProductForm;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public ProductForm $form;
    public bool $showModal = false;
    public Collection $categories;

    /**
     * Mount the component and pre-load the categories.
     */
    public function mount(): void
    {
        $this->categories = Category::orderBy('name')->get();
    }

    /**
     * Show the modal. If a productId is provided,
     * it's for editing. Otherwise, it's for creation.
     */
    #[On('show-product-modal')]
    public function show(int $productId = null): void
    {
        $this->form->reset();

        if ($productId) {
            $product = Product::find($productId);
            $this->form->setProduct($product);
        }

        $this->showModal = true;
    }

    /**
     * Save the product (create or update) and close the modal.
     */
    public function save(): void
    {
        if (isset($this->form->product)) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        $this->dispatch('product-saved');
        $this->showModal = false;
    }
}; ?>

<div>
    {{-- Dynamically set the modal title --}}
    <flux:modal
        wire:model="showModal"
        name="product-form-modal"
        :title="$form->product ? 'Modifier le Produit' : 'Nouveau Produit'"
        class="w-2xl"
    >
        <form wire:submit="save" class="space-y-4">
            {{-- Name Field --}}
            <flux:field>
                <flux:label for="name">Nom du produit</flux:label>
                <flux:input wire:model="form.name" id="name" type="text" placeholder="Ex: Wendy's Classic"/>
                <flux:error for="form.name"/>
            </flux:field>

            {{-- Category Select --}}
            <flux:field>
                <flux:label for="category_id">Catégorie</flux:label>
                <flux:select wire:model="form.category_id" id="category_id">
                    <option value="" disabled>Sélectionner une catégorie...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error for="form.category_id" />
            </flux:field>

            {{-- Description Field --}}
            <flux:field>
                <flux:label for="description">Description</flux:label>
                <flux:textarea wire:model="form.description" id="description"
                               placeholder="Une description alléchante du produit..." rows="4"/>
                <flux:error for="form.description"/>
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Price Field --}}
                <flux:field>
                    <flux:label for="price">Prix</flux:label>
                    <flux:input wire:model="form.price" id="price" type="number" step="0.01" placeholder="Ex: 12.50"/>
                    <flux:error for="form.price"/>
                </flux:field>

                {{-- Loyalty Tier Select --}}
                <flux:field>
                    <flux:label for="loyalty_tier">Niveau de Fidélité (Récompense)</flux:label>
                    <flux:select wire:model="form.loyalty_tier" id="loyalty_tier">
                        <option value="">Aucun (Produit standard)</option>
                        @foreach(config('wendys.loyalty') as $tier => $info)
                            <option value="{{ $tier }}">Niveau {{ $tier }} - {{ $info['name'] }} ({{ $info['points'] }} pts)</option>
                        @endforeach
                    </flux:select>
                    <flux:error for="form.loyalty_tier" />
                </flux:field>
            </div>

            {{-- Image Upload Field (CORRECTED) --}}
            <flux:field>
                    <flux:label for="photo">Image du produit</flux:label>
                    <input wire:model="form.photo" id="photo" type="file" class="sr-only">

                    <div class="flex items-center gap-4">
                        {{-- Show preview of new photo OR existing photo --}}
                        @if ($form->photo)
                            <img src="{{ $form->photo->temporaryUrl() }}" class="w-20 h-20 object-cover rounded-lg shadow-sm">
                        @elseif ($form->image_url)
                            <img src="{{ $form->image_url }}" class="w-20 h-20 object-cover rounded-lg shadow-sm">
                        @else
                            <div class="w-20 h-20 rounded-lg bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center">
                                <flux:icon name="photo" class="size-8 text-zinc-400" />
                            </div>
                        @endif

                        {{-- Custom Button --}}
                        <div class="flex flex-col">
                            <label for="photo" class="cursor-pointer">
                                {{-- This is now a simple span styled to look like a button --}}
                                <span class="inline-block px-4 py-2 border border-zinc-300 dark:border-zinc-700 rounded-md text-sm font-semibold bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                                    Parcourir...
                                </span>
                            </label>
                            <flux:text variant="subtle" class="mt-2 text-xs">
                                PNG, JPG (MAX. 5Mo)
                            </flux:text>
                        </div>
                    </div>

                    {{-- Loading State --}}
                    <div wire:loading wire:target="form.photo">
                        <flux:text variant="subtle" class="mt-2">Téléversement en cours...</flux:text>
                    </div>

                    <flux:error for="form.photo" />
                </flux:field>

            {{-- Switches --}}
            <div class="flex gap-6">
                <flux:field>
                    <flux:switch wire:model="form.is_available" id="is_available" label="Disponible à la vente"/>
                </flux:field>
                <flux:field>
                    <flux:switch wire:model="form.featured" id="featured" label="Mettre en avant sur la page d'accueil"/>
                </flux:field>
            </div>

            {{-- Modal Actions --}}
            <div class="flex justify-end gap-2 pt-4">
                <flux:button type="button" variant="ghost" @click="$wire.showModal = false">
                    Annuler
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Enregistrer
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
