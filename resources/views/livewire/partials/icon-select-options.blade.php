@foreach ($this->pageResults as $option)
    @php($name = $option['value'])
    <button class="ui-select-option" type="button" role="option" tabindex="-1" value="{{ $name }}"
        id="{{ $this->getId() }}-option-{{ $name }}" wire:key="icon-{{ $name }}"
        aria-selected="{{ $this->isSelected($name) ? 'true' : 'false' }}" @disabled($disabled || $readonly)>
        <span class="ui-option-text"><x-ui::icon :name="$name" />{{ $name }}</span>
        @if ($this->isSelected($name))
            <span class="ui-option-indicator"><x-ui::icon name="check" /></span>
        @endif
    </button>
@endforeach
