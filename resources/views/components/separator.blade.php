@blaze(fold: true)

@props([
    'orientation' => 'horizontal',
    'decorative' => true,
])

<div {{ $attributes->merge([
    'data-slot' => 'separator',
    'data-orientation' => $orientation,
    'role' => $decorative ? 'none' : 'separator',
    'aria-orientation' => $decorative || $orientation === 'horizontal' ? false : $orientation,
]) }}></div>
