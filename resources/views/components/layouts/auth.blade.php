<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? "Wendy's Diner" }}</title>

    @vite('resources/css/app.css')
    @fluxAppearance
</head>

<body class="bg-background text-primary-text font-sans antialiased">
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <div>
        <a href="/" wire:navigate>
            <h1 class="font-heading text-4xl text-accent-1">Wendy's Diner</h1>
        </a>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white/50 dark:bg-zinc-800/50 shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
@fluxScripts
</body>
</html>
