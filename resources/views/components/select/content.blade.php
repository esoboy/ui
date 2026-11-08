@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 0,
    'alignItemWithTrigger' => true,
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'role' => 'listbox',
    'tabindex' => '-1',
    'data-slot' => 'select-content',
    'data-align-trigger' => $alignItemWithTrigger ? 'true' : 'false',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
