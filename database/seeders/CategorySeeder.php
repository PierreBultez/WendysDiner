<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Nos Burgers Signature', 'position' => 10],
            ['name' => 'Accompagnements', 'position' => 20],
            ['name' => 'Boissons Fraîches', 'position' => 30],
            ['name' => 'Desserts Gourmands', 'position' => 40],
            ['name' => 'Menu Enfant', 'position' => 50],
        ];

        foreach ($categories as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'position' => $categoryData['position'],
            ]);
        }
    }
}
