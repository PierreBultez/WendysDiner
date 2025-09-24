<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.admin')] #[Title('Apparence')] class extends Component
{
    //
}; ?>

{{-- --- BLADE TEMPLATE (RESTRUCTURED) --- --}}
{{-- On utilise notre composant de layout pour les paramètres --}}
<x-settings.layout>
    {{-- La section est maintenant une carte unique --}}
    <div class="p-4 sm:p-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
        <header>
            <h2 class="text-lg font-medium text-primary-text dark:text-zinc-100">
                {{ __('Apparence') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Mettez à jour les paramètres d\'apparence de votre compte.') }}
            </p>
        </header>

        <div class="mt-6">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Clair') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Sombre') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('Système') }}</flux:radio>
            </flux:radio.group>
        </div>
    </div>
</x-settings.layout>
