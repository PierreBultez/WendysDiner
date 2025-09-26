@props([
    'variant' => 'default'
])

@php
    $baseClasses = 'w-full rounded-lg shadow-md flex flex-col transition-transform hover:scale-105';
    $variantClasses = [
        'default' => 'bg-white border border-primary-text/10',
        'cta' => 'bg-white justify-center items-center text-center border-2 border-dashed border-accent-2/50',
    ][$variant];
@endphp

<div {{ $attributes->merge(['class' => $baseClasses . ' ' . $variantClasses]) }}>
    {{ $slot }}
</div>
