<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Category;
use function Livewire\Volt\with;

new #[Layout('components.layouts.admin')] #[Title('Gérer les Catégories')] class extends Component {

    // Pour le tri
    public string $sortColumn = 'name';
    public string $sortDirection = 'asc';

    // Pour la suppression en masse
    public array $selectedCategories = [];
    public bool $showBulkDeleteModal = false;

    // Pour la suppression unique
    public bool $showDeleteModal = false;
    public ?Category $deletingCategory = null;

    /**
     * Fournit la liste des catégories à la vue.
     */
    public function with(): array
    {
        return [
            'categories' => Category::withCount('products')
                ->orderBy($this->sortColumn, $this->sortDirection)
                ->get(),
        ];
    }

    /**
     * Gère le tri des colonnes.
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
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $categoryId): void
    {
        $this->deletingCategory = Category::find($categoryId);
        $this->showDeleteModal = true;
    }

    /**
     * Delete the category.
     */
    public function deleteCategory(): void
    {
        if ($this->deletingCategory) {
            $this->deletingCategory->delete();
        }

        $this->showDeleteModal = false;
        $this->deletingCategory = null;
        $this->dispatch('$refresh');
    }

    // --- Bulk Delete Methods ---
    public function confirmBulkDelete(): void
    {
        $this->showBulkDeleteModal = true;
    }

    /**
     * Supprime les catégories sélectionnées.
     */
    public function deleteSelected(): void
    {
        Category::whereIn('id', $this->selectedCategories)->delete();
        $this->selectedCategories = [];
        $this->showBulkDeleteModal = false;
        $this->dispatch('$refresh');
    }

    /**
     * Écoute l'événement pour rafraîchir la liste.
     */
    #[On('category-saved')]
    public function refresh(): void
    {
        $this->dispatch('$refresh');
    }
}; ?>

<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl text-primary-text font-bold">Catégories</h1>
        <div>
            @if(count($selectedCategories) > 0)
                <flux:button
                    variant="danger"
                    icon="trash"
                    wire:click="confirmBulkDelete"
                >
                    Supprimer ({{ count($selectedCategories) }})
                </flux:button>
            @endif
            <flux:button
                variant="primary"
                icon="plus"
                wire:click="$dispatch('show-category-modal')"
            >
                Nouvelle Catégorie
            </flux:button>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="mt-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="p-4"><flux:checkbox disabled /></th>
                    {{-- Sortable Headers --}}
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('name')">
                        <span class="flex items-center gap-1">
                            Nom
                            @if($sortColumn === 'name')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                            @endif
                        </span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('slug')">
                        <span class="flex items-center gap-1">
                            Slug
                            @if($sortColumn === 'slug')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                            @endif
                        </span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('position')">
                        <span class="flex items-center gap-1">
                            Position @if($sortColumn === 'position')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                            @endif
                        </span>
                    </th>
                    <th scope="col" class="px-6 py-3 cursor-pointer" wire:click="sortBy('products_count')">
                        <span class="flex items-center gap-1">
                            Produits
                            @if($sortColumn === 'products_count')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                            @endif
                        </span>
                    </th>
                    <th scope="col" class="relative px-6 py-3 w-1"><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr wire:key="{{ $category->id }}" class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-600">
                        <td class="p-4">
                            <flux:checkbox wire:model.live="selectedCategories" value="{{ $category->id }}" />
                        </td>
                        <th scope="row" class="px-6 py-4 font-medium text-zinc-900 whitespace-nowrap dark:text-white">
                            {{ $category->name }}
                        </th>
                        <td class="px-6 py-4">{{ $category->slug }}</td>
                        <td class="px-6 py-4">{{ $category->position }}</td>
                        <td class="px-6 py-4">{{ $category->products_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <flux:button.group>
                                <flux:button size="sm" variant="ghost" icon="pencil" tooltip="Modifier" wire:click="$dispatch('show-category-modal', { categoryId: {{ $category->id }} })" />
                                <flux:button size="sm" variant="danger" icon="trash" tooltip="Supprimer" wire:click="confirmDelete({{ $category->id }})" />
                            </flux:button.group>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-zinc-500">Aucune catégorie trouvée.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:admin.categories.category-form/>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" name="delete-category-modal" title="Confirmer la Suppression">
        @if($deletingCategory)
            <p>
                Êtes-vous sûr de vouloir supprimer la catégorie <strong>"{{ $deletingCategory->name }}"</strong> ?
            </p>
            <p class="mt-2 text-sm text-zinc-600">
                Cette action est irréversible. Tous les produits associés à cette catégorie seront également supprimés.
            </p>
        @endif

        <div class="flex justify-end gap-2 pt-4">
            <flux:button type="button" variant="ghost" @click="$wire.showDeleteModal = false">
                Annuler
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="deleteCategory">
                Oui, Supprimer
            </flux:button>
        </div>
    </flux:modal>

    {{-- Bulk Delete Confirmation Modal (NEW) --}}
    <flux:modal wire:model="showBulkDeleteModal" name="bulk-delete-modal" title="Confirmer la Suppression Multiple">
        <p>
            Êtes-vous sûr de vouloir supprimer les <strong>{{ count($selectedCategories) }}</strong> catégories sélectionnées ?
        </p>
        <p class="mt-2 text-sm text-zinc-600">
            Cette action est irréversible.
        </p>
        <div class="flex justify-end gap-2 pt-4">
            <flux:button type="button" variant="ghost" @click="$wire.showBulkDeleteModal = false">
                Annuler
            </flux:button>
            <flux:button type="button" variant="danger" wire:click="deleteSelected">
                Oui, Tout Supprimer
            </flux:button>
        </div>
    </flux:modal>
</div>
