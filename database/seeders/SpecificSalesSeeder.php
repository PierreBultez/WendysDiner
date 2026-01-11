<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpecificSalesSeeder extends Seeder
{
    public function run(): void
    {
        // Cible unique demandée
        $targets = [
            ['month' => 9, 'year' => 2025, 'total' => 1489.20],
        ];

        $product = Product::first() ?? Product::factory()->create(['price' => 10.00]);

        foreach ($targets as $target) {
            $this->generateMonthlyRevenue($target['month'], $target['year'], $target['total'], $product);
        }

        $this->command->info("Ajout de 1489.20€ sur Septembre 2025 effectué !");
    }

    private function generateMonthlyRevenue(int $month, int $year, float $targetTotal, $product)
    {
        $currentTotal = 0;
        $orderCount = 15; 
        $remaining = $targetTotal;

        DB::transaction(function () use ($month, $year, $orderCount, &$remaining, $product) {
            for ($i = 0; $i < $orderCount; $i++) {
                $isLast = ($i === $orderCount - 1);
                
                if ($isLast) {
                    $amount = $remaining;
                } else {
                    $amount = round(rand(20, 150) + (rand(0, 99) / 100), 2);
                    if ($amount >= $remaining - 10) {
                        $amount = round($remaining / 2, 2);
                    }
                }

                $remaining -= $amount;

                // Dates aléatoires dans le mois
                $date = Carbon::create($year, $month, rand(1, 30), rand(11, 22), rand(0, 59));

                $order = Order::create([
                    'status' => 'terminée',
                    'total_amount' => $amount,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'created_at' => $date,
                ]);

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'method' => 'revolut',
                    'created_at' => $date,
                ]);
            }
        });
    }
}