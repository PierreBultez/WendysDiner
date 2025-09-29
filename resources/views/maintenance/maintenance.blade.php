<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wendy's Diner - Bientôt de retour !</title>

    {{-- On charge nos styles pour que la page soit cohérente avec le thème --}}
    @vite('resources/css/app.css')
    @fluxAppearance
</head>
<body class="bg-background text-primary-text font-sans antialiased">

{{-- Conteneur principal pour centrer le contenu verticalement et horizontalement --}}
<main class="min-h-screen flex items-center justify-center p-4">

    <div class="text-center max-w-lg mx-auto">

        {{-- Icône thématique --}}
        <div>
            <flux:icon name="wrench-screwdriver" class="size-16 text-accent-2/50 mx-auto" />
        </div>

        {{-- Titre principal avec la police "Caprasimo" --}}
        <h1 class="mt-6 text-5xl md:text-6xl text-accent-1">
            Une Petite Pause Cuisine
        </h1>

        {{-- Sous-titre explicatif et chaleureux --}}
        <p class="mt-4 text-lg text-primary-text/80">
            On affûte les couteaux et on prépare les frites pour le prochain service ! Notre site web fait une petite pause pour revenir encore meilleur.
        </p>
        <p class="mt-2 text-primary-text/70">
            Revenez dans un petit instant, les burgers n'attendent que vous.
        </p>

        {{-- Lien optionnel pour les réseaux sociaux --}}
        <div class="mt-8">
            <p class="text-sm text-primary-text/60">En attendant, suivez nos aventures sur :</p>
            <div class="flex justify-center space-x-4 mt-2">
                <a href="#" aria-label="Facebook" class="text-primary-text/70 hover:text-accent-1 transition-colors">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.14H6v4.44h3.5v8.4h4.44v-8.4h3.81l.47-4.44z"></path></svg>
                </a>
                <a href="#" aria-label="Instagram" class="text-primary-text/70 hover:text-accent-1 transition-colors">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6m9.65 1.5a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10m0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"></path></svg>
                </a>
            </div>
        </div>

    </div>

</main>

</body>
</html>
