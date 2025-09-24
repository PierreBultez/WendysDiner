<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(15),
            'price' => fake()->randomFloat(2, 5, 25), // Generates a price between 5.00 and 25.00
            'image_url' => fake()->imageUrl(640, 480, 'food'), // Placeholder image
            'category_id' => Category::factory(), // Creates a new category for the product, or you can pass an existing ID.
        ];
    }
}
