<?php
namespace App\Livewire\Forms\Admin\POS;

use Livewire\Form;
class PaymentForm extends Form
{
    public array $payments = [];
    public string $newPaymentMethod = 'espèces';
    public string $newPaymentAmount = '';
    public string $cashReceived = '';
}
