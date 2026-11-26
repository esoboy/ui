<div {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes, 'resetSelection') }} data-ui-overlay x-data="uiPopover"

    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'form', 'required'])->class('ui-select-body') }}>
    @php($localSelection = $mode === 'bound' && (! in_array('live', explode('.', $modelDirective ?? ''), true) || in_array('renderless', explode('.', $modelDirective ?? ''), true)))
    <x-ui::select.trigger x-on:pointerdown.capture="$el.toggleAttribute('data-touch', $event.pointerType === 'touch')" x-on:keydown.capture="$el.removeAttribute('data-touch')">
        <x-ui::button variant="outline" :size="$size" :disabled="$disabled" :id="$attributes->get('id')" class="ui-select-control" {{ $attributes->whereStartsWith('aria-') }}
            aria-haspopup="listbox" :data-size="$size" :wire:bind:data-placeholder="$localSelection && $displayValue === null ? 'value == null || ! [value].flat().length ? true : null' : null" :data-placeholder="$this->label === '' && $displayValue === null ? true : null">
            @php($displayOption = count($this->selected) <= 1 ? collect($this->choices)->first(fn ($option) => $this->isSelected($option['value'])) : null)
            <span class="ui-select-value">
                @if($displayValue !== null){{ $displayValue }}
                @elseif($localSelection)
                    @include('ui::livewire.partials.select-value')
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
    @php($native = $this->usesNativeSelect())
    @unless ($native)
        @include('ui::livewire.partials.model-field', ['reflectSelect' => true])
    @endunless
    {{-- A list select distinguishes no selection from an option whose actual value is the empty string. --}}
    @php($accepted = $mode === 'controlled' ? 'externalValue' : 'value')
    @php($nativeValues = array_map(strval(...), array_column($this->formChoices, 'value')))
    @php($sharedValues = count(array_unique($nativeValues)) !== count($nativeValues))
    @php($formKey = md5(serialize([array_column($this->choices, 'value'), $native ? null : $this->selected])))
    <select wire:key="form-select-{{ $formKey }}" @if ($native) size="2" id="{{ $this->fieldId() }}" {{ $this->fieldBinding() }} @else multiple @if (! $sharedValues) wire:bind:value="{{ $accepted }} ?? []" @endif @endif class="ui-visually-hidden" tabindex="-1" aria-hidden="true"
        @if ($attributes->has('name')) name="{{ $attributes->get('name') }}{{ $multiple ? '[]' : '' }}" @endif
        {{ $attributes->only('form') }} @required($required && ! $readonly) @disabled($disabled)
        x-on:invalid.prevent="trigger.focus()">
        @php($defaults = array_column($this->formDefaults, 'value'))
        @foreach ($this->formChoices as $choice)
            <option wire:key="current-{{ md5(serialize($choice['value'])) }}" value="{{ $choice['value'] }}" name="{{ json_encode($choice['value'], JSON_THROW_ON_ERROR) }}"
                @if ($sharedValues) x-effect="$el.selected = [$wire.{{ $accepted }}].flat().includes(@js($choice['value']))" @endif
                @selected(in_array($choice['value'], $defaults, true))>{{ $choice['label'] }}</option>
        @endforeach
    </select>
</div>
