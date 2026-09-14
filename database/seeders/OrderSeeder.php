<?php


namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'cliente@example.com')->firstOrFail();

        $products = Product::whereIn('sku', [
            'MOU-WIR-001',
            'TEC-MEC-001',
        ])->get()->keyBy('sku');

        $subtotal = 0;

        $items = [
            [
                'product' => $products['MOU-WIR-001'],
                'quantity' => 2,
            ],
            [
                'product' => $products['TEC-MEC-001'],
                'quantity' => 1,
            ],
        ];

        $order = Order::updateOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'pending',
            ],
            [
                'subtotal' => 0,
                'total' => 0,
                'currency' => 'USD',
            ]
        );

        $order->items()->delete();

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $lineSubtotal = $product->price * $quantity;
            $subtotal += $lineSubtotal;

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $lineSubtotal,
            ]);
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    }
}