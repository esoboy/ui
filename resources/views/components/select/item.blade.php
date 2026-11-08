@blaze

@props([
    'value',
])

<div {{ $attributes->merge([
    'role' => 'option',
    'tabindex' => '-1',
    'data-slot' => 'select-item',
    'data-value' => $value,
    'aria-selected' => 'false',
    'aria-disabled' => (bool) $attributes->get('disabled', false) ? 'true' : false,
]) }}>
    <span data-slot="select-item-text">{{ $slot }}</span>
    <span data-slot="select-item-indicator" @if (isset($indicator)) {{ $indicator->attributes }} @endif>@if (isset($indicator)){{ $indicator }}@else<x-ui::icon name="check" />@endif</span>
</div>
