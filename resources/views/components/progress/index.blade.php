@props(['color' => null, 'value' => null, 'max' => 100])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'progress', $attributes);
    if (!is_numeric($max) || $max <= 0 || ($value !== null && (!is_numeric($value) || $value < 0 || $value > $max))) throw new \InvalidArgumentException(__('ui::errors.progress_value'));
@endphp
<div data-esoboy-ui data-slot="progress" {{ $attributes->except(['id', 'aria-label', 'aria-labelledby', 'aria-describedby']) }}>
    {{ $slot }}
    <progress data-esoboy-ui data-slot="progress-track" max="{{ $max }}" @if($value !== null) value="{{ $value }}" @endif {{ $attributes->only(['id', 'aria-label', 'aria-labelledby', 'aria-describedby']) }}></progress>
</div>
