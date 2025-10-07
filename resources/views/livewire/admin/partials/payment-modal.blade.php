<?php
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\Order;
use Illuminate\Validation\Rule;

new class extends Component {

    public bool $showModal = false;
    public ?Order $order = null; // Il reçoit la commande à encaisser

    // --- Payment Flow Management (CORRECTED) ---
    public array $payments = [];
    public string $newPaymentMethod = 'espèces';
    public string $newPaymentAmount = '';
    public string $cashReceived = '';
    public ?string $successMessage = null;

    // --- RÈGLES DE VALIDATION ---
    public function rules(): array
    {
        return [
            'newPaymentMethod' => 'required|in:espèces,carte',
            // 'numeric' garantit que la chaîne est un nombre valide.
            'newPaymentAmount' => 'required|numeric|min:0.1|max:' . $this->getRemainingAmount(),
            // Ajout d'une règle pour cashReceived pour la robustesse
            'cashReceived' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * This listener opens the modal with a specific order to process.
     */
    #[On('show-payment-modal')]
    public function show(int $orderId): void
    {
        $this->order = Order::find($orderId);
        if ($this->order) {
            $this->payments = [];
            $this->newPaymentAmount = (string) $this->getRemainingAmount();
            $this->cashReceived = '';
            $this->showModal = true;
        }
    }

    /**
     * Add a payment line to the current order.
     */
    public function addPaymentLine(): void
    {
        $this->validate();

        $this->payments[] = [
            'method' => $this->newPaymentMethod,
            'amount' => (float) $this->newPaymentAmount,
        ];

        $remaining = $this->getRemainingAmount();
        $this->newPaymentAmount = $remaining > 0 ? (string) $remaining : '';
        $this->newPaymentMethod = 'carte';
    }

    /**
     * Remove a payment line.
     */
    public function removePaymentLine(int $index): void
    {
        if (isset($this->payments[$index])) {
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments);
        }
    }

    /**
     * The modal now has its own save method.
     */
    public function savePayments(): void
    {
        if ($this->getRemainingAmount() > 0.009) return;

        // Save the payments to the database
        foreach ($this->payments as $payment) {
            $this->order->payments()->create([
                'amount' => $payment['amount'],
                'method' => $payment['method'],
            ]);
        }

        // Update the order status
        $this->order->status = 'en cours';
        $this->order->save();

        $this->showModal = false;
        // Notify the calling component that the payment is done
        $this->dispatch('payment-saved'); // Pour la page des commandes
        $this->dispatch('order-fully-paid'); // Pour la page POS
    }

    /**
     * Calculate the amount still due.
     */
    public function getRemainingAmount(): float
    {
        if (!$this->order) return 0.0;
        $paid = collect($this->payments)->sum('amount');
        return round($this->order->total_amount - $paid, 2);
    }

    /**
     * Calculate change to be given back.
     */
    public function getChange(): float
    {
        if (!$this->cashReceived) return 0.0;
        $cashReceived = (float) $this->cashReceived;
        $cashPayments = collect($this->payments)->where('method', 'espèces')->sum('amount');
        if ($cashReceived >= $cashPayments) {
            return round($cashReceived - $cashPayments, 2);
        }
        return 0.0;
    }
}; ?>

{{-- --- NEW: PAYMENT MODAL --- --}}
<div>
    @if($order) {{-- Only render the modal if an order is loaded --}}
        <flux:modal
            wire:model="showModal"
            name="payment-modal"
            title="Encaisser la Commande #{{ $order->id }}"
            class="max-w-xl"
        >
            {{-- Order Summary --}}
            <div class="bg-zinc-50 dark:bg-zinc-700 p-4 rounded-lg">
                <div class="flex justify-between text-2xl font-bold text-primary-text">
                    <span>Total à Payer</span>
                    <span>{{ number_format($order->total_amount, 2, ',', ' ') }} €</span>
                </div>
                @if($this->getRemainingAmount() > 0.009)
                    <div class="flex justify-between text-lg font-bold text-accent-1 mt-2">
                        <span>Reste à Payer</span>
                        <span>{{ number_format($this->getRemainingAmount(), 2, ',', ' ') }} €</span>
                    </div>
                @else
                    <div class="flex justify-between text-lg font-bold text-lime-600 mt-2">
                        <span>Total Payé</span>
                        <span>{{ number_format($order->total_amount, 2, ',', ' ') }} €</span>
                    </div>
                @endif
            </div>

            {{-- Payment Lines --}}
            <div class="mt-4 space-y-2">
                @foreach($payments as $index => $payment)
                    <div class="flex items-center justify-between p-2 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                        <span class="capitalize">{{ $payment['method'] }}</span>
                        <span class="font-semibold">{{ number_format($payment['amount'], 2, ',', ' ') }} €</span>
                        <button wire:click="removePaymentLine({{ $index }})">
                            <flux:icon name="x-mark" class="size-4 text-red-500" />
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Add Payment Form --}}
            @if($this->getRemainingAmount() > 0.009)
                <div wire:key="add-payment-form" class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label for="payment_method">Moyen de paiement</flux:label>
                            <flux:select wire:model.live="newPaymentMethod" id="payment_method">
                                <option value="espèces">Espèces</option>
                                <option value="carte">Carte Bancaire</option>
                            </flux:select>
                        </flux:field>
                        <flux:field>
                            <flux:label for="payment_amount">Montant</flux:label>
                            <flux:input wire:model.blur="newPaymentAmount" id="payment_amount" type="number" step="0.01" />
                        </flux:field>
                    </div>
                    <div class="text-right mt-2">
                        <flux:button wire:click="addPaymentLine" variant="primary" size="sm">Ajouter Paiement</flux:button>
                    </div>
                </div>
            @endif

            {{-- Cash Payment & Change Calculation --}}
            @if(collect($payments)->where('method', 'espèces')->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-dashed">
                    <flux:field>
                        <flux:label for="cash_received">Montant reçu en espèces</flux:label>
                        <flux:input wire:model.blur="cashReceived" id="cash_received" type="number" step="0.01" placeholder="Ex: 20.00" />
                    </flux:field>
                    @if($this->getChange() > 0)
                        <div class="mt-2 text-center text-xl font-bold p-3 bg-accent-2/20 text-accent-2 rounded-lg">
                            Monnaie à rendre : {{ number_format($this->getChange(), 2, ',', ' ') }} €
                        </div>
                    @endif
                </div>
            @endif

            {{-- Modal Footer --}}
            <div class="flex justify-end gap-2 pt-6">
                <flux:button type="button" variant="ghost" @click="$wire.showModal = false">Annuler</flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    class="!py-3"
                    wire:click="savePayments"
                    :disabled="$this->getRemainingAmount() > 0.009"
                >
                    Encaisser la Commande
                </flux:button>
            </div>
        </flux:modal>
    @endif
        {{-- --- NOTRE TOAST PERSONNALISÉ --- --}}
        @if ($successMessage)
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => { $wire.set('successMessage', null) }, 3000)"
                x-show="show"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white dark:bg-zinc-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <flux:icon name="check-circle" class="size-6 text-green-500" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-primary-text dark:text-white">
                                Succès !
                            </p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $successMessage }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="$wire.set('successMessage', null)" class="inline-flex text-zinc-400 hover:text-zinc-500">
                                <span class="sr-only">Fermer</span>
                                <flux:icon name="x-mark" class="size-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
</div>
