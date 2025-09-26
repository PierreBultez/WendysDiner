@props([
    'title' => '',
    'subtitle' => '',
    'titleClasses' => 'text-primary-text', // Couleur par défaut
    'subtitleClasses' => 'text-primary-text/70' // Couleur par défaut
])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if($title)
        <h2 {{ $attributes->merge(['class' => 'text-4xl md:text-5xl font-bold ' . $titleClasses]) }}>
            {{ $title }}
        </h2>
    @endif

    @if($subtitle)
            <p {{ $attributes->merge(['class' => 'mt-2 ' . $subtitleClasses]) }}>
                {{ $subtitle }}
            </p>
    @endif

    {{-- Ce slot permet d'ajouter du contenu supplémentaire si nécessaire --}}
    {{ $slot }}
</div>
