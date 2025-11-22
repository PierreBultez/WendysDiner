<?php

namespace App\Livewire\Forms\Admin;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Product;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm extends Form
{
    // Property to hold the existing product model when editing.
    public ?Product $product = null;

    #[Validate('required|string|min:2|max:255')]
    public string $name = '';

    #[Validate('string|min:2')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('required|integer|exists:categories,id')]
    public string $category_id = '';

    #[Validate('nullable|image|max:5120')]
    public ?TemporaryUploadedFile $photo = null;

    #[Validate('boolean')]
    public bool $featured = false;

    #[Validate('boolean')]
    public bool $is_available = true;

    // We keep the image_url to reset it if needed, but it's not a form field anymore.
    public string $image_url = '';

    /**
     * Set the product to edit and fill the form fields.
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->featured = $product->featured;
        $this->is_available = $product->is_available;
        $this->image_url = $product->image_url;
    }

    /**
     * Create a new product.
     */
    public function store(): void
    {
        $this->validate();

        $imageUrl = $this->handleImageUpload();

        Product::create([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'image_url' => $imageUrl,
            'featured' => $this->featured,
            'is_available' => $this->is_available,
        ]);

        $this->reset();
    }

    /**
     * Update the existing product.
     */
    public function update(): void
    {
        $this->validate();

        $imageUrl = $this->handleImageUpload();

        $this->product->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'image_url' => $imageUrl,
            'featured' => $this->featured,
            'is_available' => $this->is_available,
        ]);

        $this->reset();
    }

    /**
     * Handle the image upload and return the final image URL.
     */
    private function handleImageUpload(): string
    {
        // If a new photo is uploaded, store it and return the new path.
        if ($this->photo) {
            $path = $this->photo->store('products', 'public');
            return '/storage/' . $path;
        }

        // If we are editing and no new photo is uploaded, keep the existing image URL.
        if ($this->product) {
            return $this->product->image_url;
        }

        // Otherwise, return the default placeholder.
        return '/images/placeholders/default-product.png';
    }
}
