@blaze

@props(['action'])
@php
    if (! in_array($action, ['previous', 'next', 'finish', 'close'], true)) {
        throw new \InvalidArgumentException('Unknown tour action.');
    }
@endphp
{!! \Esoboy\Ui\Support\ControlSlot::bind((string) $slot, $attributes, ['data-tour-action' => $action, 'wire:click' => $action]) !!}
