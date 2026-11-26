@props([
    'options' => [['value' => 10, 'label' => '10'], ['value' => 25, 'label' => '25'], ['value' => 50, 'label' => '50']],
    'popupClass' => '',
    'value', 'initialValue',
    'emptyTitle' => null,
    'emptyDescription' => null,
])
@php
    $select = $attributes->except('wire:key');
    if ($attributes->has('wire:key')) $select = $select->merge(['wire:key' => 'page-size:'.$attributes->get('wire:key')]);
    $props = get_defined_vars();
    foreach (['value' => 'value', 'initialValue' => 'initial-value'] as $prop => $name) {
        if (array_key_exists($prop, $props) || array_key_exists($name, $props)) {
            $select = $select->merge([$name => array_key_exists($prop, $props) ? $props[$prop] : $props[$name]], escape: false);
        }
    }
@endphp
<div {{ $attributes->only('wire:key') }} class="ui-data-table-page-size">
    <span aria-hidden="true">{{ __('ui::ui.table_per_page') }}</span>
    <x-ui::select :options="$options" :empty-title="$emptyTitle" :empty-description="$emptyDescription"
        :aria-label="__('ui::ui.page_size')" :popup-class="trim('ui-popup-centered '.$popupClass)" :attributes="$select">{{ $slot }}</x-ui::select>
</div>
