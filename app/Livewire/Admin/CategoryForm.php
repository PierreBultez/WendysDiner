<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryForm extends Form
{
    // Property to hold the existing category model when editing.
    public ?Category $category = null;

    #[Validate]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    /**
     * Set the validation rules.
     * We use a method to make the 'name' rule dynamic.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                // This rule ensures the name is unique,
                // but ignores the current category's name when updating.
                Rule::unique('categories')->ignore($this->category),
            ],
        ];
    }

    /**
     * Set the category to edit and fill the form fields.
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
    }

    /**
     * Create a new category from the form data.
     */
    public function store(): void
    {
        // Validate the form data.
        $this->validate();

        // Create the category.
        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
        ]);

        // Reset the form fields.
        $this->reset();
    }

    /**
     * Update the existing category.
     */
    public function update(): void
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
        ]);

        $this->reset();
    }
}
