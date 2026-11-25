<div data-ui-overlay x-data="uiNativeSelect"
    x-on:reset.window="if ($event.target === $refs.field.form) $nextTick(() => { if (!$event.defaultPrevented && $el.isConnected) $wire.resetSelection() })"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'form', 'required'])->class('ui-select-body') }}>
    <x-ui::select.trigger x-on:pointerdown.capture="$el.toggleAttribute('data-touch', $event.pointerType === 'touch')" x-on:keydown.capture="$el.removeAttribute('data-touch')">
        <x-ui::button variant="outline" :size="$size" :disabled="$disabled" :id="$attributes->get('id')" class="ui-select-control" {{ $attributes->whereStartsWith('aria-') }}
            aria-haspopup="listbox" :data-size="$size" :data-placeholder="$this->label === '' && $displayValue === null ? true : null">
            @php($displayOption = count($this->selected) <= 1 ? collect($this->choices)->first(fn ($option) => $this->isSelected($option['value'])) : null)
            <span class="ui-select-value">
                @if($displayValue !== null){{ $displayValue }}
                @elseif(isset($displayOption['description']))
                    @include('ui::livewire.partials.option-label', ['option' => $displayOption])
                @else @isset($displayOption['icon'])<x-ui::icon :name="$displayOption['icon']" />@endisset{{ $this->label !== '' ? $this->label : ($placeholder !== '' ? $placeholder : ($displayOption['label'] ?? __('ui::ui.select'))) }}@endif
            </span>
            <x-ui::icon name="chevron-down" class="ui-select-chevron" />
        </x-ui::button>
    </x-ui::select.trigger>
    <div id="{{ $this->getId() }}-listbox" data-side="{{ $side }}" data-preferred-side="{{ $side }}" data-align="{{ $align }}" data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-align-trigger="{{ $classic ? 'false' : 'true' }}" data-ui-panel popover="auto" role="listbox" tabindex="-1"
        aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" @if ($required) aria-required="true" @endif class="ui-select-content ui-popup ui-list-popup {{ $popupClass }}" x-on:keydown="navigate($event)"
        x-on:toggle="toggled($event)"
        x-on:pointermove="if ($event.pointerType === 'mouse') { pointerFocus = true; $event.target.closest('[role=option]:not(:disabled)')?.focus({ preventScroll: true }) }"
        x-on:pointerleave="if (pointerFocus) $el.focus({ preventScroll: true })">
        <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">
            @include('ui::livewire.partials.select-list')
        </x-ui::scroll-area>
    </div>
    {{-- A list select distinguishes no selection from an option whose actual value is the empty string. --}}
    <select multiple x-ref="field" class="ui-visually-hidden" tabindex="-1" aria-hidden="true"
        @if ($attributes->has('name')) name="{{ $attributes->get('name') }}{{ $multiple ? '[]' : '' }}" @endif
        {{ $attributes->only('form') }} @required($required && ! $readonly) @disabled($disabled)
        x-on:invalid.prevent="trigger.focus()" x-effect="reflectField($el, {{ $mode === 'controlled' ? '$wire.externalValue' : '$wire.value' }})">
        @foreach ($this->formDefaults as $choice)
            <option wire:key="default-{{ md5(serialize($choice['value'])) }}" value="{{ $choice['value'] }}" selected
                data-reset-option>{{ $choice['label'] }}</option>
        @endforeach
        @foreach ($this->formChoices as $choice)
            <option wire:key="current-{{ md5(serialize($choice['value'])) }}" value="{{ $choice['value'] }}"
                data-choice="{{ json_encode($choice['value'], JSON_THROW_ON_ERROR) }}">{{ $choice['label'] }}</option>
        @endforeach
    </select>
</div>
