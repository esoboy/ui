@blaze(fold: true)

@props([
    'span' => 1,
])

<div {{ $attributes->merge(['data-slot' => 'description-list-item', 'style' => $span > 1 ? "--span: {$span};" : false]) }}>{{ $slot }}</div>
