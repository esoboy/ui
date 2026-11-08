@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn: stars in the warning color, a radio group to the keyboard. Clicking the chosen star clears it; readonly shows fractions. --}}
@props([
    'max' => 5,
    'size' => 'default',
    'readonly' => false,
    'disabled' => false,
    'value' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$value = is_numeric($value) ? $value + 0 : null;
$max = max(1, (int) $max);
$settings = compact('max', 'size', 'disabled');
$settings['validation'] = $state->attributes();
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('rating'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

@if ($readonly)
<span {{ $attributes->except('name')->merge(['role' => 'img', 'data-slot' => 'rating', 'data-size' => $size, 'data-readonly' => true, 'aria-label' => __('ui::rating.label', ['value' => $value ?? 0, 'max' => $max])]) }}>
    @foreach (range(1, $max) as $i)
    <span data-slot="rating-item" style="--rating-fill: {{ round(max(0, min(1, ($value ?? 0) - $i + 1)) * 100) }}%"><x-ui::icon name="star" fill="currentColor" stroke="none" /><x-ui::icon name="star" fill="currentColor" stroke="none" /></span>
    @endforeach
</span>
@else
@livewire('ui.rating', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], key('ui-rating-'.$key))
@endif
<x-ui::field.error :state="$state" />
<x-ui::extras />
