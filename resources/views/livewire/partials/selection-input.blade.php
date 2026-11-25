@if ($attributes->get('name'))
    @foreach ($selection ?? $this->selected as $selected)
        <input wire:key="selection-{{ md5(serialize($selected)) }}" type="hidden" name="{{ $attributes->get('name') }}{{ is_array($this->current) ? '[]' : '' }}" value="{{ $selected }}"
            {{ $attributes->only('form') }} @disabled($disabled)>
    @endforeach
@endif
