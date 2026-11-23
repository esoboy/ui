@props(['value' => null, 'max' => 100])
@php
    if (!is_numeric($max) || $max <= 0 || ($value !== null && (!is_numeric($value) || $value < 0 || $value > $max))) throw new \InvalidArgumentException(__('ui::errors.progress_value'));
@endphp
<div {{ $attributes->except(['id', 'aria-label', 'aria-labelledby', 'aria-describedby'])->class('ui-progress') }}>
    {{ $slot }}
    <progress max="{{ $max }}" @if($value !== null) value="{{ $value }}" @endif {{ $attributes->only(['id', 'aria-label', 'aria-labelledby', 'aria-describedby'])->class('ui-progress-track') }}></progress>
</div>
