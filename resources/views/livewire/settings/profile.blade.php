<?php

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')]
#[Title('Mon profil')]
class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard.index', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<x-settings.layout>
    <div class="space-y-6">
        {{-- Card 1: Update Profile Information --}}
        <div class="p-4 sm:p-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
            <header>
                <h2 class="text-lg font-medium text-primary-text dark:text-zinc-100">
                    {{ __('Informations du Profil') }}
                </h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Mettez à jour votre nom et votre adresse e-mail.') }}
                </p>
            </header>

            <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
                <flux:input wire:model="name" :label="__('Nom')" type="text" required autofocus autocomplete="name"/>
                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email"/>
                    @if (auth()->user() instanceof MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                        {{-- ... (Email verification logic remains the same) ... --}}
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        {{ __('Enregistrer') }}
                    </flux:button>
                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Enregistré.') }}
                    </x-action-message>
                </div>
            </form>
        </div>

        {{-- Card 2: Delete User Form --}}
        <div class="p-4 sm:p-8 bg-white dark:bg-zinc-800 shadow sm:rounded-lg">
            <livewire:settings.delete-user-form/>
        </div>
    </div>
</x-settings.layout>
