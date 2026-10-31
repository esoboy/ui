@blaze

@aware([
    'orientation' => 'horizontal',
])

@props([
    'withHandle' => false,
])

<div {{ $attributes->merge([
    'role' => 'separator',
    'tabindex' => '0',
    'data-slot' => 'resizable-handle',
    'aria-orientation' => $orientation === 'horizontal' ? 'vertical' : 'horizontal',
]) }} wire:ignore.self>
    @if ($withHandle)
    <div></div>
    @endif
</div>
