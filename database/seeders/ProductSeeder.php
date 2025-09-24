<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch categories at once to avoid multiple queries in the loop
        $categories = Category::pluck('id', 'name');

        $products = [
            // Burgers
            [
                'name' => 'Wendy\'s Classic',
                'description' => 'Le burger qui a fait notre réputation. Steak de bœuf frais, cheddar maturé, salade croquante, tomate, oignons rouges et notre sauce secrète inimitable.',
                'price' => 12.90,
                'image_url' => '/images/products/wendys-classic.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            [
                'name' => 'Baconator Deluxe',
                'description' => 'Pour les amateurs de bacon. Double steak, double cheddar, et six tranches de bacon fumé croustillant. Un monstre de saveur.',
                'price' => 14.50,
                'image_url' => '/images/products/baconator-deluxe.png',
                'category_name' => 'Nos Burgers Signature'
            ],
            // Accompagnements
            [
                'name' => 'Frites Maison au Cheddar',
                'description' => 'Nos frites dorées coupées à la main, nappées d\'une sauce cheddar onctueuse et parsemées de ciboulette fraîche.',
                'price' => 6.50,
                'image_url' => '/images/products/frites-cheddar.png',
                'category_name' => 'Accompagnements'
            ],
            [
                'name' => 'Onion Rings "50s Style"',
                'description' => 'Des rondelles d\'oignon épaisses, panées et frites à la perfection. Croustillantes à l\'extérieur, fondantes à l\'intérieur.',
                'price' => 5.50,
                'image_url' => '/images/products/onion-rings.png',
                'category_name' => 'Accompagnements'
            ],
            // Boissons
            [
                'name' => 'Milkshake Vanille Vintage',
                'description' => 'Un classique intemporel. Crème glacée à la vanille de Madagascar, lait frais, et une touche de chantilly maison.',
                'price' => 7.00,
                'image_url' => '/images/products/milkshake-vanille.png',
                'category_name' => 'Boissons Fraîches'
            ],
            [
                'name' => 'Coca-Cola Cherry en bouteille verre',
                'description' => 'Le goût authentique du Coca-Cola avec une note de cerise, servi dans sa bouteille en verre iconique.',
                'price' => 4.50,
                'image_url' => '/images/products/coca-cherry.png',
                'category_name' => 'Boissons Fraîches'
            ],
        ];

        foreach ($products as $productData) {
            // Find the category ID from the fetched collection
            $categoryId = $categories[$productData['category_name']] ?? null;

            if ($categoryId) {
                Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'image_url' => $productData['image_url'],
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
