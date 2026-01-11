<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Expense;
use App\Models\Supplier;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] class extends Component {
    use WithPagination;

    public $amount = '';
    public $expense_date = '';
    public $description = '';
    public $supplier_id = '';
    public $new_supplier_name = '';

    public function mount()
    {
        $this->expense_date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'new_supplier_name' => 'required_without:supplier_id|nullable|string|max:255',
        ]);

        $supplierId = $this->supplier_id;

        if (empty($supplierId) && !empty($this->new_supplier_name)) {
            $supplier = Supplier::create(['name' => $this->new_supplier_name]);
            $supplierId = $supplier->id;
        }

        if (!$supplierId) {
            $this->addError('supplier_id', 'Veuillez sélectionner ou créer un fournisseur.');
            return;
        }

        Expense::create([
            'supplier_id' => $supplierId,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'description' => $this->description,
        ]);

        $this->reset(['amount', 'description', 'supplier_id', 'new_supplier_name']);
        
        // Reset date to today
        $this->expense_date = now()->format('Y-m-d');
        
        $this->dispatch('close-modal', 'create-expense');
    }

    public function with()
    {
        return [
            'expenses' => Expense::with('supplier')->latest('expense_date')->paginate(10),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <flux:heading size="xl">Dépenses</flux:heading>
            <flux:subheading>Gérez les dépenses et les fournisseurs du restaurant.</flux:subheading>
        </div>
        <flux:modal.trigger name="create-expense">
            <flux:button variant="primary" icon="plus">Ajouter une dépense</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden bg-white dark:bg-zinc-800 shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                    <th class="px-6 py-3 text-xs font-semibold uppercase text-zinc-500 tracking-wider">Date</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase text-zinc-500 tracking-wider">Fournisseur</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase text-zinc-500 tracking-wider">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-zinc-500 tracking-wider">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($expenses as $expense)
                    <tr wire:key="{{ $expense->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $expense->expense_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $expense->supplier->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $expense->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-medium text-zinc-900 dark:text-zinc-100 tabular-nums">
                            {{ number_format($expense->amount, 2, ',', ' ') }} €
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            Aucune dépense enregistrée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $expenses->links() }}
    </div>

    {{-- Create Expense Modal --}}
    <flux:modal name="create-expense" class="min-w-[22rem] space-y-6">
        <div>
            <flux:heading size="lg">Nouvelle Dépense</flux:heading>
            <flux:subheading>Ajouter une nouvelle dépense au registre.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:input type="date" wire:model="expense_date" label="Date de la dépense" />

            <div class="space-y-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <flux:label>Fournisseur</flux:label>
                
                <flux:select wire:model.live="supplier_id" placeholder="Choisir un fournisseur existant...">
                    <flux:select.option value="">Sélectionner...</flux:select.option>
                    @foreach($suppliers as $supplier)
                        <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if(empty($supplier_id))
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-zinc-200 dark:border-zinc-700"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-zinc-50 dark:bg-zinc-800 px-2 text-xs text-zinc-500">OU CRÉER UN NOUVEAU</span>
                        </div>
                    </div>

                    <flux:input wire:model="new_supplier_name" placeholder="Nom du nouveau fournisseur" />
                @endif
            </div>

            <flux:input type="number" step="0.01" wire:model="amount" label="Montant (€)" placeholder="0.00" />

            <flux:textarea wire:model="description" label="Description (Optionnel)" rows="3" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
