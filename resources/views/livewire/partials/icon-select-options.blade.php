@foreach ($this->pageResults as $option)
    @php($name = $option['value'])
    <button type="button" role="option" tabindex="-1" data-esoboy-ui data-slot="select-option" value="{{ $name }}"
        id="{{ $this->getId() }}-option-{{ $name }}" wire:key="icon-{{ $name }}"
        aria-selected="{{ $this->isSelected($name) ? 'true' : 'false' }}" @disabled($disabled || $readonly)>
        <span data-esoboy-ui data-slot="option-text"><x-ui::icon :name="$name" />{{ $name }}</span>
        @if ($this->isSelected($name))
            <span data-esoboy-ui data-slot="option-indicator"><x-ui::icon name="check" /></span>
        @endif
    </button>
@endforeach
