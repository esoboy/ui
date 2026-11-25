@php
    $score = $this->current;
    $name = $attributes->get('name') ?? 'rating-'.$this->getId();
    $locked = $disabled || $readonly;
@endphp
<div role="radiogroup" x-data x-on:reset.window="if ($event.target === $el.querySelector('input')?.form) $nextTick(() => { if (!$event.defaultPrevented) $wire.resetScore() })" {{ $attributes->whereDoesntStartWith(['wire:model', 'wire:loading', 'wire:target'])->except(['name', 'required'])->class('ui-rating')->merge(['data-size' => $size, 'aria-readonly' => $readonly ? 'true' : null, 'aria-disabled' => $disabled ? 'true' : null]) }}>
    @for ($rating = 1; $rating <= $max; $rating++)
        <label wire:key="star-{{ $rating }}">
            <input type="radio" name="{{ $name }}" {{ $attributes->only('form') }} value="{{ $rating }}"
                @checked($score !== null && (int) $score === $rating) {{ $attributes->whereStartsWith(['wire:loading', 'wire:target']) }} @disabled($locked) @required($attributes->get('required'))
                wire:click.prevent="choose({{ $rating }})" class="ui-visually-hidden">
            <span class="ui-rating-star" aria-hidden="true"><x-ui::icon name="star" /></span>
            <span class="ui-visually-hidden">{{ __('ui::ui.rating_value', ['value' => $rating, 'max' => $max]) }}</span>
        </label>
    @endfor
    @if ($readonly && ! $disabled && $attributes->has('name'))
        <input type="hidden" {{ $attributes->only(['name', 'form']) }} value="{{ $score }}" @disabled($score === null)>
    @endif
</div>
