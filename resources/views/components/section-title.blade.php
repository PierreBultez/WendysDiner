@props([
    'title' => '',
    'subtitle' => '',
    'titleClasses' => 'text-primary-text',
    'subtitleClasses' => 'text-primary-text/70',
    'tag' => 'h2' // La balise par défaut est h2
])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    @if($title)
        {{-- On utilise la balise dynamique --}}
        <{{ $tag }} {{ $attributes->merge(['class' => 'text-4xl md:text-5xl font-bold ' . $titleClasses]) }}>
        {{ $title }}
</{{ $tag }}>
@endif

@if($subtitle)
    <p {{ $attributes->merge(['class' => 'mt-2 ' . $subtitleClasses]) }}>
        {{ $subtitle }}
    </p>
    @endif

    {{ $slot }}
    </div>
