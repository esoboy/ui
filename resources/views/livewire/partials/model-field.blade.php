@if ($this instanceof \Esoboy\Ui\Livewire\PanelState && $kind !== 'tabs')
    <input type="checkbox" hidden data-panel-field @checked($this->current) @disabled($disabled || $readonly)
        @if ($mode === 'controlled') wire:click.prevent="selectPanel(!externalValue)" @else {{ $this->fieldBinding() }} @endif>
@elseif ($mode === 'controlled' && $this instanceof \Esoboy\Ui\Livewire\PanelState)
    <ui-model-field hidden id="{{ $this->fieldId() }}" data-initial="null" wire:input="selectPanel($event.detail)"></ui-model-field>
@elseif ($mode !== 'controlled')
    <ui-model-field id="{{ $this->fieldId() }}" hidden data-initial="{{ json_encode($this->current, JSON_THROW_ON_ERROR) }}"
        @if ($reflectSelect ?? false)
            x-on:input.self="const select = $el.nextElementSibling; for (const item of select.options) item.selected = false; for (const value of [$event.detail].flat()) { const option = select.options.namedItem(JSON.stringify(value)); if (option) { select.add(option); option.selected = true } }"
        @endif
        @if ($formField ?? false) data-form-field {{ $attributes->only(['name', 'form']) }} @disabled($disabled) @if ($requiredField && ! $readonly) data-required-message="{{ __('ui::ui.required_value') }}" data-min-values="{{ ($range ?? false) ? 2 : 1 }}"
            x-on:invalid.prevent="$el.parentElement.querySelector('button:enabled:not([hidden]), input:enabled:not([hidden],[type=hidden])')?.focus()" @endif @endif
        {{ $this->fieldBinding() }}>
        @if (($choicesForField = $this->multipleChoices()) !== null)
            <select multiple hidden>
                @foreach ($choicesForField as $choice)
                    <option value="{{ json_encode($choice, JSON_THROW_ON_ERROR) }}" name="{{ json_encode($choice, JSON_THROW_ON_ERROR) }}" @selected(in_array($choice, $this->selected, true))></option>
                @endforeach
            </select>
        @endif
    </ui-model-field>
@endif
