@php
    $max = max(1, (int) ($settings['max'] ?? 5));
    $size = $settings['size'] ?? 'default';
    $disabled = $settings['disabled'] ?? false;
    $score = is_numeric($value) ? $value + 0 : null;
    $focusable = min($max, max(1, (int) round($score ?? 1)));
    $name = $attributes->get('name');
@endphp
<eso-rating {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge([
    'role' => 'radiogroup',
    'data-slot' => 'rating',
    'data-size' => $size,
    'data-value' => $score,
    'data-name' => $name,
    'data-disabled' => $disabled,
    'x-data' => 'uiRating',
    'x-on:ui-model-changed.self' => 'notifyModel()',
    'x-on:focusout' => '!$el.contains($event.relatedTarget) && blurModel()',
    'x-on:keydown' => 'key($event)',
    ...($settings['validation'] ?? []),
]) }}>
    @foreach (range(1, $max) as $i)
    <button type="button" role="radio" data-slot="rating-item" data-value="{{ $i }}" aria-checked="{{ $score !== null && (int) round($score) === $i ? 'true' : 'false' }}" aria-label="{{ __('ui::rating.label', ['value' => $i, 'max' => $max]) }}" tabindex="{{ $i === $focusable ? 0 : -1 }}" style="--rating-fill: {{ $score !== null && $i <= $score ? 100 : 0 }}%" @disabled($disabled) wire:click="choose({{ $i }})"><x-ui::icon name="star" fill="currentColor" stroke="none" /><x-ui::icon name="star" fill="currentColor" stroke="none" /></button>
    @endforeach
    @include('ui::livewire.form-value')
</eso-rating>
