@php
    $decorated = [];
    $nullLabel = null;
    foreach ($this->choices as $option) {
        if ($option['value'] === null) {
            $nullLabel = $option['label'];
        }
        if (isset($option['description']) || isset($option['icon'])) {
            $decorated[] = $option;
        }
    }
    $fallback = $placeholder !== '' ? $placeholder : ($nullLabel ?? __('ui::ui.select'));
    $separator = __('ui::ui.list_separator');
    $selectedLabels = array_column($this->selectedChoices, 'label');
    $positions = $multiple ? max(1, count($this->choices)) : 1;
    $showDecoration = $displayOption !== null && (isset($displayOption['description']) || isset($displayOption['icon']));
@endphp
@foreach ($decorated as $option)
    <span class="ui-select-value" wire:key="trigger-option-{{ md5(serialize($option['value'])) }}"
        wire:show="[value].flat().length === 1 && [value].flat()[0] === @js($option['value'])"
        @style(['display: none' => ! $showDecoration || $displayOption['value'] !== $option['value']])>
        @include('ui::livewire.partials.option-label', ['option' => $option])
    </span>
@endforeach
<span @if ($decorated !== []) wire:show="[value].flat().length !== 1 || ! @js(array_column($decorated, 'value')).includes([value].flat()[0])" @style(['display: none' => $showDecoration]) @endif>@for ($index = 0; $index < $positions; $index++)@php
        $item = $multiple ? 'value?.['.$index.']' : 'value';
        $label = "\$wire.\$el.querySelector('select.ui-visually-hidden')?.options.namedItem(JSON.stringify(".$item."))?.label";
        $text = $index === 0
            ? $label.' || '.\Illuminate\Support\Js::from($fallback)
            : $item." == null ? '' : ".\Illuminate\Support\Js::from($separator)." + (".$label." ?? '')";
    @endphp<span wire:text="{{ $text }}">{{ isset($selectedLabels[$index]) ? ($index === 0 ? '' : $separator).$selectedLabels[$index] : ($index === 0 ? $fallback : '') }}</span>@endfor</span>
