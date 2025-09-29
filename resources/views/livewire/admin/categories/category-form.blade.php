<?php

use App\Livewire\Forms\Admin\CategoryForm;
use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public CategoryForm $form;

    public bool $showModal = false;

    /**
     * Show the modal. If a categoryId is provided,
     * it's for editing. Otherwise, it's for creation.
     */
    #[On('show-category-modal')]
    public function show(int $categoryId = null): void
    {
        $this->form->reset();

        if ($categoryId) {
            $category = Category::find($categoryId);
            $this->form->setCategory($category);
        }

        $this->showModal = true;
    }

    /**
     * Save the category (create or update) and close the modal.
     */
    public function save(): void
    {
        if (isset($this->form->category)) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        $this->dispatch('category-saved');

        $this->showModal = false;
    }
}; ?>

<div>
    <flux:modal
        wire:model="showModal"
        name="category-form-modal"
        :title="$form->category ? 'Modifier la Catégorie' : 'Nouvelle Catégorie'"
        class="w-xl"
    >
        <form wire:submit="save" class="space-y-4">
            {{-- Form fields (no changes here) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label for="name">Nom de la catégorie</flux:label>
                    <flux:input wire:model="form.name" id="name" type="text" placeholder="Ex: Burgers"/>
                    <flux:error for="form.name"/>
                </flux:field>

                <flux:field>
                    <flux:label for="type">Type de Catégorie (pour POS)</flux:label>
                    <flux:select wire:model="form.type" id="type">
                        <option value="">Aucun</option>
                        <option value="burger">Burger</option>
                        <option value="accompagnement">Accompagnement</option>
                        <option value="boisson">Boisson</option>
                        <option value="sauce">Sauce</option>
                        <option value="dessert">Dessert</option>
                        <option value="snack">Snacks</option>
                        <option value="enfant">Enfants</option>
                    </flux:select>
                    <flux:error for="form.type" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label for="position">Position</flux:label>
                <flux:input wire:model="form.position" id="position" type="number" min="0" />
                <flux:error for="form.position" />
            </flux:field>

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
