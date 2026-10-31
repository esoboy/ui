@blaze(fold: true)

@props([
    'ratio' => 1,
])

<div {{ $attributes->merge(['data-slot' => 'aspect-ratio', 'style' => "--ratio: {$ratio};"]) }}>{{ $slot }}</div>
