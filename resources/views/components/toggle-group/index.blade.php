@blaze

@props([
    'type' => 'single',
    'variant' => 'default',
    'size' => 'default',
    'spacing' => 2,
    'orientation' => 'horizontal',
    'defaultValue' => null,
])

@php
// A single group is a radio group whose pressed item can be clicked off again.
$tag = $type === 'multiple' ? 'eso-checkbox-group' : 'eso-radio-group';
@endphp

<{{ $tag }} {{ $attributes->merge([
    'role' => 'group',
    'data-slot' => 'toggle-group',
    'data-variant' => $variant,
    'data-size' => $size,
    'data-spacing' => $spacing,
    'data-orientation' => $orientation,
    'data-deselectable' => $type !== 'multiple',
    'style' => '--gap: '.$spacing.';',
]) }}>{{ $slot }}</{{ $tag }}>
