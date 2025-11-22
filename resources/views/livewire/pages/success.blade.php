<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Title("Confirmation - Wendy's Diner")] class extends Component {
    //
}; ?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-800 p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <div class="mb-6 flex justify-center">
            <div class="rounded-full bg-green-100 p-4">
                <flux:icon name="check-circle" class="size-16 text-green-500" />
            </div>
        </div>
        
        <h1 class="text-3xl font-bold text-primary-text mb-4">Commande Confirmée !</h1>
        <p class="text-zinc-600 dark:text-zinc-300 mb-8">
            Merci pour votre commande. Nous allons la préparer avec soin.<br>
            Vous recevrez un email de confirmation sous peu.
        </p>
        
        <a href="{{ route('menu') }}" class="inline-block bg-accent-1 text-white font-bold py-3 px-8 rounded-lg hover:bg-accent-1/90 transition-colors">
            Retour à la carte
        </a>
    </div>
</div>
