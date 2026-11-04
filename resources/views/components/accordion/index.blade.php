@blaze

@props([
    'defaultValue' => null,
    'multiple' => false,
])

{{-- Items are native <details>; <eso-accordion> names them so only one stays open unless multiple. --}}
<eso-accordion {{ $attributes->merge(['data-slot' => 'accordion', 'data-multiple' => $multiple]) }}>{{ $slot }}</eso-accordion>
