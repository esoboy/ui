@php
    $inputAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, ['id', 'name', 'form', 'required', 'maxlength', 'minlength', 'placeholder', 'autocomplete', 'rows', 'cols', 'wrap', 'spellcheck', 'tabindex', 'enterkeyhint', 'inputmode', 'autofocus']);
@endphp
<div {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes) }}
    {{ $attributes->except(array_keys($inputAttributes->all()))->whereDoesntStartWith('wire:model')->class('ui-mentions-body') }}
    x-data="uiOptions" x-on:keydown="navigate($event)"
    @if ($mode === 'controlled') x-on:mention-proposed="const input = $el.querySelector('textarea'); if (input.value === $event.detail.draft) input.value = $wire.externalValue ?? ''" @endif>
    @php
        $field = $mode === 'controlled'
            ? $inputAttributes->except('name')->merge(['wire:input' => 'proposeText($event.target.value)'])
            : $inputAttributes->merge($this->fieldBinding()->all());
    @endphp
    <x-ui::textarea :size="$size" :value="($mode === 'controlled' ? $externalValue : $this->initialFormValue) ?? ''"
        :disabled="$disabled" :readonly="$readonly" :attributes="$field" />
    @if ($mode === 'controlled' && $inputAttributes->has('name'))
        <input type="hidden" {{ $inputAttributes->only(['name', 'form']) }} value="{{ $externalValue }}" @disabled($disabled)>
    @endif
    @if ($this->queryToken !== null && ! $disabled && ! $readonly)
        <div class="ui-mentions-suggestions"
            wire:key="mentions-{{ $this->getId() }}-{{ md5($this->queryToken) }}">
            @if ($this->suggestions === [])
                @include('ui::partials.empty-state', ['emptyMessage' => $options === [] ? 'members' : 'member_matches'])
            @else
                <x-ui::scroll-area class="ui-list-scroll" tabindex="-1" role="listbox">
                    @foreach ($this->suggestions as $option)
                        <x-ui::button role="option" variant="ghost" class="ui-transfer-item" :disabled="$disabled || $readonly || $option['disabled']"
                            :data-source="$this->current" :data-replacement="json_encode($this->completion($option['value']), JSON_THROW_ON_ERROR)"
                            x-on:click="const input = $el.closest('.ui-mentions-body').querySelector('textarea'); if (input.value === $el.dataset.source) { input.value = JSON.parse($el.dataset.replacement); input.dispatchEvent(new Event('input', { bubbles: true })); input.dispatchEvent(new Event('change', { bubbles: true })) }"
                            wire:key="mention-{{ serialize($option['value']) }}">{{ $option['label'] }}</x-ui::button>
                    @endforeach
                </x-ui::scroll-area>
            @endif
        </div>
    @endif
</div>
