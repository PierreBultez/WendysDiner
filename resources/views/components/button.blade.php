@props([
    'variant' => 'default'
])

@php
    $variantClasses = match ($variant) {
        'primary' => 'px-8 py-3 text-lg font-bold',
        'ghost-accent' => '!text-accent-2 !border-accent-2 hover:!bg-accent-2 hover:!text-primary-text',
        'danger' => '!bg-accent-1 !border-accent-1 !text-white hover:!bg-accent-1/90',
        default => '',
    };

    // Ici, nous décidons quelle "variant" de base de Flux UI utiliser.
    $fluxVariant = match ($variant) {
        // Pour nos variantes custom, on se base sur une variante neutre de Flux.
        'ghost-accent' => 'ghost',
        'danger' => 'default',
        // Pour les autres, on passe le nom directement (ex: 'primary', 'subtle', etc.).
        default => $variant,
    };
@endphp

{{-- On passe la 'variant' directement à flux:button --}}
<flux:button
    :variant="$fluxVariant"
    {{ $attributes->merge(['class' => $variantClasses]) }}
>
    {{ $slot }}
</flux:button>
