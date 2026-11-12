@blaze(fold: true)

@props([
    'align' => 'inline-start',
])

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'input-group-addon', 'data-align' => $align]) }}>{{ $slot }}</div>
