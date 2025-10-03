<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Wendy's Diner" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

    <title>{{ $title ?? "Wendy's Diner" }}</title>

    @vite('resources/css/app.css')
    @fluxAppearance
</head>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-BE5JN18EEG"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-BE5JN18EEG');
</script>

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
