<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? "Wendy's Diner" }}</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Wendy's Diner" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

    {{-- This will load our app.css file with all the theme styles --}}
    @vite('resources/css/app.css')

    {{-- Flux UI directives --}}
    @fluxAppearance
</head>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-FFK72XRNF1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-FFK72XRNF1');
</script>

<body class="min-h-screen flex flex-col">

{{-- HEADER & NAVIGATION --}}
<flux:header class="sticky top-0 z-50 border-b border-primary-text/10 bg-background/80 backdrop-blur-lg">
    <a href="/" wire:navigate class="font-heading text-4xl text-accent-1 transition-colors hover:text-accent-1/80 px-2">
        Wendy's Diner
    </a>

    <flux:spacer />

    {{-- Desktop Navigation (hidden on small screens) --}}
    <flux:navbar class="hidden md:flex">
        <flux:navbar.item href="/" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Accueil</flux:navbar.item>
        <flux:navbar.item href="/histoire" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>L'histoire</flux:navbar.item>
        <flux:navbar.item href="/carte" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>La carte</flux:navbar.item>
        <flux:navbar.item href="/fidelite" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Le Club</flux:navbar.item>
        <flux:navbar.item href="/infos" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Infos</flux:navbar.item>
        
        @auth
            <flux:navbar.item href="{{ route('my-orders') }}" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Mon Espace</flux:navbar.item>
            {{-- Show Admin Dashboard only if user is verified (and potentially admin logic later) --}}
            <flux:navbar.item href="{{ route('dashboard.index') }}" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Admin</flux:navbar.item>
        @else
            <flux:navbar.item href="{{ route('login') }}" class="hover:!bg-accent-2 hover:!text-background" wire:navigate>Connexion</flux:navbar.item>
        @endauth
    </flux:navbar>

    {{-- Mobile Navigation (hamburger menu, visible only on small screens) --}}
    <div class="flex items-center md:hidden">
        <flux:dropdown align="end">
            {{-- Le bouton de déclenchement doit être un enfant DIRECT du dropdown --}}
            <flux:button
                variant="ghost"
                icon="bars-3"
                aria-label="Ouvrir le menu"
            />

            {{-- Le contenu du menu va dans le slot "content" --}}
                <flux:menu class="w-80 !p-5 !bg-background">
                    <flux:menu.item href="/" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Accueil</flux:menu.item>
                    <flux:menu.item href="/histoire" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>L'histoire</flux:menu.item>
                    <flux:menu.item href="/carte" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>La carte</flux:menu.item>
                    <flux:menu.item href="/fidelite" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Le Club</flux:menu.item>
                    <flux:menu.item href="/infos" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Infos</flux:menu.item>
                    
                    @auth
                        <flux:menu.item href="{{ route('my-orders') }}" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Mon Espace</flux:menu.item>
                        <flux:menu.item href="{{ route('dashboard.index') }}" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Admin</flux:menu.item>
                    @else
                        <flux:menu.item href="{{ route('login') }}" class="text-xl hover:!bg-accent-2 hover:text-background" wire:navigate>Connexion</flux:menu.item>
                    @endauth
                </flux:menu>
        </flux:dropdown>
    </div>
</flux:header>

{{-- MAIN CONTENT AREA --}}
<main class="flex-grow">
    {{ $slot }}
</main>

{{-- FOOTER --}}
<x-partials.footer />

<livewire:components.global-cart />

{{-- Flux UI & Livewire scripts --}}
@fluxScripts
@stack('scripts')
</body>
</html>
