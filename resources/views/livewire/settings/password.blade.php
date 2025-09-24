<?php

// --- PHP LOGIC (UNCHANGED) ---
// La logique Volt pour la mise à jour du mot de passe est conservée.
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.admin')] #[Title('Mot de Passe')] class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

{{-- --- BLADE TEMPLATE (RESTRUCTURED) --- --}}
{{-- On utilise notre composant de layout pour les paramètres --}}
<x-settings.layout>
    {{-- La section est maintenant une carte unique --}}
    <div class="p-4 sm:p-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
        <header>
            <h2 class="text-lg font-medium text-primary-text dark:text-zinc-100">
                {{ __('Mettre à jour le mot de passe') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester en sécurité.') }}
            </p>
        </header>

        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Mot de passe actuel')"
                type="password"
                required
                autocomplete="current-password"
            />

            <flux:input
                wire:model="password"
                :label="__('Nouveau mot de passe')"
                type="password"
                required
                autocomplete="new-password"
            />

            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmer le mot de passe')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Enregistrer') }}
                </flux:button>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Enregistré.') }}
                </x-action-message>
            </div>
        </form>
    </div>
</x-settings.layout>
