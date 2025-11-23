<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('my-orders', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Créer un compte')" :description="__('Entrez vos informations ci-dessous pour créer votre compte')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Nom')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Nom complet')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Adresse email')"
            type="email"
            required
            autocomplete="email"
            :placeholder="__('email@example.com')"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Mot de passe')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Mot de passe')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmer le mot de passe')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirmer le mot de passe')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Créer mon compte') }}
            </flux:button>
        </div>
    </form>

    <div class="text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('Vous avez déjà un compte ?') }}
        <flux:link :href="route('login')" wire:navigate class="font-bold">
            {{ __('Se connecter') }}
        </flux:link>
    </div>
</div>
