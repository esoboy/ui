@php($showClear = $clearable && $label !== '' && ! $disabled && ! $readonly)
<div data-esoboy-ui data-slot="picker-control" @if ($showClear) data-clearable @endif>
    <x-ui::popover.trigger>
        <x-ui::button variant="outline" data-picker-trigger :data-slot="$picker.'-trigger'" :disabled="$disabled"
            :id="$attributes->get('id')" :class="$attributes->get('class')" {{ $attributes->whereStartsWith('aria-') }}
            :aria-invalid="$attributes->get('aria-invalid', $attributes->get('invalid') ? 'true' : null)"
            :data-placeholder="$label === '' ? true : null" aria-haspopup="dialog">
            <x-ui::icon :name="$icon" />
            <span>{{ $label !== '' ? $label : $attributes->get('placeholder', $prompt) }}</span>
            @unless ($showClear)<x-ui::icon name="chevron-down" />@endunless
        </x-ui::button>
    </x-ui::popover.trigger>
    @if ($showClear)
        <x-ui::button data-slot="picker-clear" variant="ghost" size="icon-xs" :aria-label="__('ui::ui.clear')"
            wire:click="clear" x-on:click="trigger.focus({ preventScroll: true })"><x-ui::icon name="x" /></x-ui::button>
    @endif
</div>
