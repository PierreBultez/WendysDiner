<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? "Wendy's Diner" }}</title>

    {{-- This will load our app.css file with all the theme styles --}}
    @vite('resources/css/app.css')

    {{-- Flux UI directives --}}
    @fluxAppearance
</head>
<body class="min-h-screen flex flex-col">

{{-- HEADER & NAVIGATION --}}
<flux:header class="sticky top-0 z-50 border-b border-primary-text/10 bg-background/80 backdrop-blur-lg">
    {{-- Brand/Logo --}}
    <flux:brand href="/" wire:navigate name="Wendy's Diner" class="font-heading text-xl !text-accent-1" />

    <flux:spacer />

    {{-- Desktop Navigation (hidden on small screens) --}}
    <flux:navbar class="hidden md:flex">
        <flux:navbar.item href="/" wire:navigate>Accueil</flux:navbar.item>
        <flux:navbar.item href="/histoire" wire:navigate>L'histoire</flux:navbar.item>
        <flux:navbar.item href="/carte" wire:navigate>La carte</flux:navbar.item>
        <flux:navbar.item href="/infos" wire:navigate>Infos</flux:navbar.item>
        <flux:navbar.item href="{{ route('dashboard.index') }}" wire:navigate>Dashboard</flux:navbar.item>
    </flux:navbar>

    {{-- Mobile Navigation (hamburger menu, visible only on small screens) --}}
    <div class="flex items-center md:hidden">
        <flux:dropdown align="end">
            <flux:button
                variant="ghost"
                icon="bars-3"
                aria-label="Ouvrir le menu"
            />
            <x-slot:content>
                <flux:navmenu class="w-56">
                    <flux:navmenu.item href="/" wire:navigate>Accueil</flux:navmenu.item>
                    <flux:navmenu.item href="/histoire" wire:navigate>L'histoire</flux:navmenu.item>
                    <flux:navmenu.item href="/carte" wire:navigate>La carte</flux:navmenu.item>
                    <flux:navmenu.item href="/infos" wire:navigate>Infos</flux:navmenu.item>
                    <flux:navmenu.item href="{{ route('dashboard.index') }}" wire:navigate>Admin</flux:navmenu.item>
                </flux:navmenu>
            </x-slot:content>
        </flux:dropdown>
    </div>
</flux:header>

{{-- MAIN CONTENT AREA --}}
<main class="flex-grow">
    {{ $slot }}
</main>

{{-- FOOTER --}}
<x-partials.footer />

{{-- Flux UI & Livewire scripts --}}
@fluxScripts
@stack('scripts')
</body>
</html>
