@blaze(fold: true)

{{-- Placed at the pointer by ui.js, alignOffset below it (shadcn's 4). --}}
@props([
    'alignOffset' => 4,
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'context-menu-content',
    'data-align-offset' => $alignOffset,
]) }}>{{ $slot }}</div>
