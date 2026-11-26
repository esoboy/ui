@foreach ($this->pageResults as $option)
    @php($name = $option['value'])
    <button class="ui-select-option" type="button" role="option" tabindex="-1" value="{{ $name }}"
        id="{{ $this->getId() }}-option-{{ $name }}" wire:key="icon-{{ $name }}"
        {{ $this->choiceAttributes($name) }} x-on:click="choosing = true; if (!@js($searchInPopup)) $el.closest('[data-ui-overlay]').querySelector('input[role=combobox]').value = $el.value; close(true)" aria-selected="{{ $this->isSelected($name) ? 'true' : 'false' }}" @disabled($disabled || $readonly)>
        <span class="ui-option-text"><x-ui::icon :name="$name" />{{ $name }}</span>
        @if ($this->isSelected($name))
            <span class="ui-option-indicator"><x-ui::icon name="check" /></span>
        @endif
    </button>
@endforeach
