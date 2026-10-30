@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 6,
    'alignOffset' => 0,
])

{{-- Placed against the input group or the chips (data-combobox-anchor). --}}
<div {{ $attributes->merge([
    'popover' => 'manual',
    'role' => 'listbox',
    'data-slot' => 'combobox-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
