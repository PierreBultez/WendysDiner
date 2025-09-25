<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Product;
use function Livewire\Volt\with;
use Livewire\Attributes\On;

new #[Layout('components.layouts.admin')] #[Title('Gérer les Produits')] class extends Component {

    // Sorting
    public string $sortColumn = 'name';
    public string $sortDirection = 'asc';

    // Bulk Delete
    public array $selectedProducts = [];
    public bool $showBulkDeleteModal = false;

    // Single Delete
    public bool $showDeleteModal = false;
    public ?Product $deletingProduct = null;

    /**
     * Provide the list of products to the view.
     * Eager load the 'category' relationship to prevent N+1 queries.
     */
    /**
     * Listen for the 'product-saved' event and refresh the list.
     */
    #[On('product-saved')]
    public function with(): array
    {
        return [
            'products' => Product::with('category')
                ->orderBy($this->sortColumn, $this->sortDirection)
                ->get(),
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

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $productId): void
    {
        $this->deletingProduct = Product::find($productId);
        $this->showDeleteModal = true;
    }

    /**
     * Delete the product.
     */
    public function deleteProduct(): void
    {
        if ($this->deletingProduct) {
            $this->deletingProduct->delete();
        }

        $this->showDeleteModal = false;
        $this->deletingProduct = null;
        $this->dispatch('$refresh');
    }

    // --- Bulk Delete ---
    public function confirmBulkDelete(): void
    {
        $this->showBulkDeleteModal = true;
    }

    public function deleteSelected(): void
    {
        Product::whereIn('id', $this->selectedProducts)->delete();
        $this->selectedProducts = [];
        $this->showBulkDeleteModal = false;
        $this->dispatch('$refresh');
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-3xl text-primary-text font-bold">Produits</h1>
        <div class="flex items-center gap-2">
            @if(count($selectedProducts) > 0)
                <flux:button
                    variant="danger"
                    icon="trash"
                    wire:click="confirmBulkDelete"
                >
                    Supprimer ({{ count($selectedProducts) }})
                </flux:button>
            @endif
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="$dispatch('show-product-modal')"
            >
                Nouveau Produit
            </flux:button>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="mt-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="p-4"><flux:checkbox disabled /></th>
                    <th scope="col" class="px-6 py-3">Image</th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('name')">
                        <span class="flex items-center gap-1">
                            Nom @if($sortColumn === 'name') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif
                        </span>
                    </th>
                    <th scope="col" class="px-6 py-3">Catégorie</th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('price')">
                         <span class="flex items-center gap-1">
                            Prix @if($sortColumn === 'price') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif
                        </span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('featured')">
                         <span class="flex items-center gap-1">
                            En Avant @if($sortColumn === 'featured') <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" /> @endif
                        </span>
                    </th>
                    <th scope="col" class="relative px-6 py-3 w-1"><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr wire:key="{{ $product->id }}" class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-600">
                        <td class="p-4">
                            <flux:checkbox wire:model.live="selectedProducts" value="{{ $product->id }}" />
                        </td>
                        <td class="p-4">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md">
                        </td>
                        <th scope="row" class="px-6 py-4 font-medium text-zinc-900 whitespace-nowrap dark:text-white">
                            {{ $product->name }}
                        </th>
                        <td class="px-6 py-4">{{ $product->category->name }}</td>
                        <td class="px-6 py-4">{{ number_format($product->price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4">
                            @if($product->featured)
                                <flux:badge color="lime" variant="solid" icon="star">{{ __('Oui') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" variant="outline">{{ __('Non') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <flux:button.group>
                                <flux:button size="sm" variant="ghost" icon="pencil" tooltip="Modifier" wire:click="$dispatch('show-product-modal', { productId: {{ $product->id }} })" />
                                <flux:button size="sm" variant="danger" icon="trash" tooltip="Supprimer" wire:click="confirmDelete({{ $product->id }})" />
                            </flux:button.group>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-zinc-500">Aucun produit trouvé.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:admin.products.product-form />

    {{-- Single Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" name="delete-product-modal" title="Confirmer la Suppression">
        @if($deletingProduct)
            <p>
                Êtes-vous sûr de vouloir supprimer le produit <strong>"{{ $deletingProduct->name }}"</strong> ?
            </p>
            <p class="mt-2 text-sm text-zinc-600">
                Cette action est irréversible.
            </p>
        @endif

        <div class="flex justify-end gap-2 pt-4">
            <flux:button type="button" variant="ghost" @click="$wire.showDeleteModal = false">
                Annuler
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="deleteProduct">
                Oui, Supprimer
            </flux:button>
        </div>
    </flux:modal>

    {{-- Bulk Delete Modal --}}
    <flux:modal wire:model="showBulkDeleteModal" name="bulk-delete-modal" title="Confirmer la Suppression Multiple">
        <p>Êtes-vous sûr de vouloir supprimer les <strong>{{ count($selectedProducts) }}</strong> produits sélectionnés ?</p>
        <p class="mt-2 text-sm text-zinc-600">Cette action est irréversible.</p>
        <div class="flex justify-end gap-2 pt-4">
            <flux:button type="button" variant="ghost" @click="$wire.showBulkDeleteModal = false">Annuler</flux:button>
            <flux:button type="button" variant="danger" wire:click="deleteSelected">Oui, Tout Supprimer</flux:button>
        </div>
    </flux:modal>
</div>
