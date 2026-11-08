@blaze

@props(['action'])
@php
    if (! in_array($action, ['previous', 'next', 'close', 'zoom-in', 'zoom-out', 'rotate-left', 'rotate-right', 'reset'], true)) {
        throw new \InvalidArgumentException('Unknown image preview action.');
    }
    $binding = ['data-image-preview' => $action];
    if (in_array($action, ['previous', 'next'], true)) {
        $binding['wire:click'] = $action;
    }
@endphp
{!! \Esoboy\Ui\Support\ControlSlot::bind((string) $slot, $attributes, $binding) !!}
