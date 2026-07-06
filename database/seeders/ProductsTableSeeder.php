<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        DB::table('products')->insert([
            [
                'product_name' => 'Red Chair',
                'description' => 'Comfortable red chair with wooden legs.',
                'price' => 49.99,
                'image' => 'https://via.placeholder.com/120x80?text=Chair',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_name' => 'Blue Lamp',
                'description' => 'Stylish blue lamp for bedside tables.',
                'price' => 29.50,
                'image' => 'https://via.placeholder.com/120x80?text=Lamp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_name' => 'Coffee Mug',
                'description' => 'Ceramic mug, 350ml capacity.',
                'price' => 9.99,
                'image' => 'https://via.placeholder.com/120x80?text=Mug',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_name' => 'Notebook',
                'description' => 'Hardcover notebook, 200 pages.',
                'price' => 12.00,
                'image' => 'https://via.placeholder.com/120x80?text=Notebook',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with USB receiver.',
                'price' => 24.75,
                'image' => 'https://via.placeholder.com/120x80?text=Mouse',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
