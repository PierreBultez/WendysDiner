<div>
    <h1 class="text-3xl text-primary-text font-bold">Paramètres</h1>
    <p class="mt-1 text-zinc-600 dark:text-zinc-400">Gérez votre profil et les paramètres de votre compte.</p>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-8">
        {{-- Colonne de Navigation Secondaire --}}
        <div class="md:col-span-1">
            <flux:navlist>
                <flux:navlist.item href="{{ route('profile.edit') }}" wire:navigate :current="request()->routeIs('profile.edit')">
                    Profil
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('password.edit') }}" wire:navigate :current="request()->routeIs('password.edit')">
                    Mot de passe
                </flux:navlist.item>

                <flux:navlist.item href="{{ route('two-factor.show') }}" wire:navigate :current="request()->routeIs('two-factor.show')">
                    Two-Factor Auth
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('appearance.edit') }}" wire:navigate :current="request()->routeIs('appearance.edit')">
                    Apparence
                </flux:navlist.item>

            </flux:navlist>
        </div>

        {{-- Colonne de Contenu Principal --}}
        <div class="md:col-span-3">
            {{ $slot }}
        </div>
    </div>
</div>
