@props(['max' => 5, 'value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'rating', get_defined_vars(), ['max' => $max]);
    $score = $binding->config['value'];
    $name = $attributes->get('name') ?? $binding->key ?? 'rating-'.\Illuminate\Support\Str::uuid();
    $locked = $binding->config['disabled'] || $binding->config['readonly'];
@endphp
<div data-slot="rating" role="radiogroup" x-data="uiRating" data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
    @if($binding->key) wire:key="{{ $binding->key }}" @endif
    x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)"
    {{ $attributes->except(['name', 'wire:key', 'readonly', 'disabled', 'required'])->merge(['id' => $binding->id, 'aria-readonly' => $binding->config['readonly'] ? 'true' : null, 'aria-disabled' => $binding->config['disabled'] ? 'true' : null]) }}>
    @for ($rating = 1; $rating <= $max; $rating++)
        <label>
            <input type="radio" name="{{ $name }}" value="{{ $rating }}"
                @checked($score !== null && (int) $score === $rating) @disabled($locked) @required($attributes->get('required'))
                x-on:click="choose($el)" x-on:keydown.space.prevent="if (!$event.repeat) choose($el)" x-on:input.stop x-on:change.stop class="ui-visually-hidden">
            <span class="ui-rating-star" aria-hidden="true"><x-ui::icon name="star" /></span>
            <span class="ui-visually-hidden">{{ __('ui::ui.rating_value', ['value' => $rating, 'max' => $max]) }}</span>
        </label>
    @endfor
    @if ($binding->config['readonly'] && !$binding->config['disabled'] && $score !== null && $attributes->has('name'))
        <input type="hidden" name="{{ $attributes->get('name') }}" value="{{ $score }}">
    @endif
</div>
