<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Livewire\Admin\CategoryForm;
use App\Models\Category;

new class extends Component
{
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
            <flux:field>
                <flux:label for="name">Nom de la catégorie</flux:label>
                <flux:input wire:model="form.name" id="name" type="text" placeholder="Ex: Burgers" />
                <flux:error for="form.name" />
            </flux:field>

            <flux:field>
                <flux:label for="description">Description (Optionnel)</flux:label>
                <flux:textarea wire:model="form.description" id="description" placeholder="Une brève description de la catégorie..." />
                <flux:error for="form.description" />
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
