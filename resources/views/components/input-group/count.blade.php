@blaze

@props([
    'max' => null,
])

{{-- shadcn's "0/280 characters" in a block-end addon, kept up to date. It counts as Laravel's max rule does; max is a soft limit that turns it red, without it the control's maxlength is the limit. wire:ignore leaves it to its script; wire:key keeps morphs matching it by the id its script gives it. --}}
<eso-input-group-count {{ $attributes->merge([
    'data-slot' => 'input-group-count',
    'data-max' => $max,
    'data-label' => __('ui::input-group.count'),
    'data-label-max' => __('ui::input-group.count-max'),
]) }} wire:key="input-group-count" wire:ignore>{{ $max ? __('ui::input-group.count-max', ['count' => 0, 'max' => $max]) : __('ui::input-group.count', ['count' => 0]) }}</eso-input-group-count>
