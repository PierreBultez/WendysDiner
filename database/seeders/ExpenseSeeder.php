<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Expense;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create realistic suppliers
        $suppliersList = [
            ['name' => 'Metro Cash & Carry', 'contact_info' => 'service-client@metro.fr'],
            ['name' => 'Promocash', 'contact_info' => 'contact@promocash.com'],
            ['name' => 'Boulangerie du Coin', 'contact_info' => '01 23 45 67 89'],
            ['name' => 'Primeur Express', 'contact_info' => 'commandes@primeurexpress.fr'],
            ['name' => 'EDF Pro', 'contact_info' => 'service-pro@edf.fr'],
            ['name' => 'Orange Business', 'contact_info' => 'support@orange.com'],
        ];

        $suppliers = [];
        foreach ($suppliersList as $data) {
            $suppliers[] = Supplier::firstOrCreate(['name' => $data['name']], $data);
        }

        // 2. Generate expenses for the last 12 months
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        $endDate = Carbon::now();

        $expenses = [];

        // Loop through each month
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            
            // Randomly decide if there's an expense today (e.g., 30% chance)
            if (rand(1, 100) <= 30) {
                $supplier = $suppliers[array_rand($suppliers)];
                
                // Determine expense type based on supplier for better realism
                $amount = match($supplier->name) {
                    'EDF Pro' => rand(150, 400), // High utility bills
                    'Orange Business' => rand(40, 90), // Internet/Phone
                    'Metro Cash & Carry', 'Promocash' => rand(200, 1500), // Big restock
                    'Boulangerie du Coin' => rand(20, 80), // Daily bread
                    'Primeur Express' => rand(50, 200), // Fresh produce
                    default => rand(10, 100),
                };

                // Add some randomness to the amount (cents)
                $amount += (rand(0, 99) / 100);

                $expenses[] = [
                    'supplier_id' => $supplier->id,
                    'amount' => $amount,
                    'expense_date' => $date->format('Y-m-d'),
                    'description' => $this->getDescriptionForSupplier($supplier->name),
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
        }

        // Insert in chunks to be efficient
        foreach (array_chunk($expenses, 50) as $chunk) {
            Expense::insert($chunk);
        }
    }

    private function getDescriptionForSupplier(string $name): string
    {
        return match($name) {
            'EDF Pro' => 'Facture électricité',
            'Orange Business' => 'Abonnement Internet & Téléphone',
            'Metro Cash & Carry' => 'Réassort général (Sec & Frais)',
            'Promocash' => 'Boissons et Entretien',
            'Boulangerie du Coin' => 'Pain burgers et hot-dogs',
            'Primeur Express' => 'Fruits et Légumes frais',
            default => 'Achat divers',
        };
    }
}