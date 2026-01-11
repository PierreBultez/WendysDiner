<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        // Vérifier qu'il y a des produits
        if (Product::count() === 0) {
            $this->command->error("Aucun produit trouvé. Veuillez d'abord exécuter le ProductSeeder.");
            return;
        }

        // Récupérer les données de base
        $products = Product::all();
        // On récupère quelques utilisateurs ou on en crée des fictifs s'il n'y en a pas assez
        $users = User::limit(50)->get();
        if ($users->count() < 5) {
             $users = User::factory(10)->create();
        }

        // Période : 12 derniers mois jusqu'à hier (pour ne pas polluer les tests d'aujourd'hui en cours)
        $start = Carbon::now()->subMonths(12)->startOfMonth();
        $end = Carbon::yesterday();
        $period = CarbonPeriod::create($start, $end);

        $this->command->info("Génération des ventes du " . $start->format('d/m/Y') . " au " . $end->format('d/m/Y') . "...");

        DB::transaction(function () use ($period, $products, $users) {
            foreach ($period as $date) {
                // Variation réaliste : Plus de commandes le Vendredi (5) et Samedi (6)
                $dayOfWeek = $date->dayOfWeek;
                $isWeekend = $dayOfWeek === 5 || $dayOfWeek === 6;
                
                // Nombre de commandes aléatoire pour ce jour
                // Moyenne semaine : 3-8 commandes
                // Moyenne week-end : 8-15 commandes
                $min = $isWeekend ? 8 : 3;
                $max = $isWeekend ? 15 : 8;
                
                // Petite variation saisonnière (plus de ventes en été ?)
                if ($date->month >= 6 && $date->month <= 8) {
                    $max += 5;
                }

                $orderCount = rand($min, $max);

                for ($i = 0; $i < $orderCount; $i++) {
                    $this->createOrder($date->copy(), $products, $users);
                }
            }
        });

        $this->command->info("Terminé ! Données de vente générées.");
    }

    private function createOrder(Carbon $date, $products, $users)
    {
        // Heure aléatoire (Lunch 11h-14h ou Dîner 18h-22h)
        $hour = rand(0, 1) ? rand(11, 14) : rand(18, 22);
        $minute = rand(0, 59);
        $createdAt = $date->setTime($hour, $minute);

        // Client aléatoire (ou null pour invité)
        $user = rand(0, 10) > 2 ? $users->random() : null;

        // Création de la commande
        $order = Order::create([
            'user_id' => $user?->id,
            'status' => 'terminée', // Pour compter dans le CA
            'total_amount' => 0, // Sera calculé après
            'pickup_time' => $createdAt->copy()->addMinutes(30),
            'delivery_method' => rand(0, 1) ? 'pickup' : 'delivery',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        // Ajout des produits (1 à 4 articles)
        $itemCount = rand(1, 4);
        $totalAmount = 0;
        $orderItems = [];

        for ($j = 0; $j < $itemCount; $j++) {
            $product = $products->random();
            $quantity = rand(1, 2);
            $price = $product->price * $quantity;

            $orderItems[] = [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'notes' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            $totalAmount += $price;
        }

        OrderItem::insert($orderItems);

        // Mise à jour du total
        $order->update(['total_amount' => $totalAmount]);

        // Ajout du paiement
        Payment::create([
            'order_id' => $order->id,
            'amount' => $totalAmount,
            'method' => rand(0, 10) > 3 ? 'revolut' : 'especes',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
