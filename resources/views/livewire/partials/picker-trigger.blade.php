@php($showClear = $clearable && $label !== '' && ! $disabled && ! $readonly)
<div class="ui-picker-control" @if ($showClear) data-clearable @endif>
    <x-ui::popover.trigger>
        <x-ui::button variant="outline" :size="$size" data-picker-trigger :disabled="$disabled"
            :id="$attributes->get('id')" class="ui-picker-trigger ui-{{ $picker }}-trigger" {{ $attributes->whereStartsWith('aria-') }}
            :aria-invalid="$attributes->get('aria-invalid', $attributes->get('invalid') ? 'true' : null)"
            :data-placeholder="$label === '' ? true : null" aria-haspopup="dialog">
            <x-ui::icon :name="$icon" />
            <span>{{ $label !== '' ? $label : $attributes->get('placeholder', $prompt) }}</span>
            @unless ($showClear)<x-ui::icon name="chevron-down" />@endunless
        </x-ui::button>
    </x-ui::popover.trigger>
    @if ($showClear)
        <x-ui::button class="ui-picker-clear" variant="ghost" size="icon-xs" :aria-label="__('ui::ui.clear')"
            wire:click="clear" x-on:click="trigger.focus({ preventScroll: true })"><x-ui::icon name="x" /></x-ui::button>
    @endif
</div>
