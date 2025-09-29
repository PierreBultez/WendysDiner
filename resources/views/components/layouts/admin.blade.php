<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? "Admin - Wendy's Diner" }}</title>

    @vite('resources/css/app.css')
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-100 dark:bg-zinc-900">
{{-- Sidebar for navigation --}}
<flux:sidebar sticky collapsible="mobile" class="bg-white dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="{{ route('home') }}"
            wire:navigate
            name="Wendy's Diner"
            class="!font-heading !text-accent-1"
        />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="chart-bar" href="{{ route('dashboard.index') }}" wire:navigate :current="request()->routeIs('dashboard.index')">
            Tableau de bord
        </flux:sidebar.item>
        <flux:sidebar.item icon="banknotes" href="{{ route('dashboard.pos') }}" wire:navigate :current="request()->routeIs('dashboard.pos')">
            Caisse
        </flux:sidebar.item>
        <flux:sidebar.item icon="shopping-bag" href="{{ route('dashboard.orders.index') }}" wire:navigate :current="request()->routeIs('dashboard.orders.index')">
            Commandes
        </flux:sidebar.item>
        <flux:sidebar.item icon="rectangle-stack" href="{{ route('dashboard.categories.index') }}" wire:navigate :current="request()->routeIs('dashboard.categories.index')">
            Catégories
        </flux:sidebar.item>
        <flux:sidebar.item icon="cake" href="{{ route('dashboard.products.index') }}" wire:navigate :current="request()->routeIs('dashboard.products.index')">
            Produits
        </flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    {{-- User dropdown at the bottom --}}
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile
            avatar="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=57402F&background=FDFBF2"
            name="{{ auth()->user()->name }}"
            description="{{ auth()->user()->email }}"
        />
        <flux:menu>
            <flux:menu.item icon="user-circle" href="{{ route('profile.edit') }}" wire:navigate>Mon Profil</flux:menu.item>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item tag="button" type="submit" icon="arrow-right-start-on-rectangle">
                    Déconnexion
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

{{-- Header for mobile (hamburger menu) --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
</flux:header>

{{-- Main content area --}}
<flux:main>
    <div class="p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </div>
</flux:main>

@fluxScripts
</body>
</html>
