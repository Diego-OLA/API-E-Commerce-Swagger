<?php


namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Lenovo',
                'description' => 'Laptop para trabajo y estudio.',
                'sku' => 'LAP-LEN-001',
                'price' => 899.99,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Mouse inalámbrico',
                'description' => 'Mouse ergonómico inalámbrico.',
                'sku' => 'MOU-WIR-001',
                'price' => 24.99,
                'stock' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Teclado mecánico',
                'description' => 'Teclado mecánico RGB.',
                'sku' => 'TEC-MEC-001',
                'price' => 79.99,
                'stock' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}