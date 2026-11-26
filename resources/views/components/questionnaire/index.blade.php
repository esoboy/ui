@props([
    'items' => [],
    'item' => null,
    'initialItem' => '',
    'shortcuts' => '',
    'submitLabel' => '',
    'layout' => 'standard',
    'showProgress' => true,
    'progressClass' => '',
    'value',
    'initialValue',
    'emptyTitle' => null,
    'emptyDescription' => null,
])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'questionnaire', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-questionnaire ui-questionnaire-field') }}>
    @livewire(
        'ui::questionnaire',
        [
            ...$binding->parameters,
            'items' => $items,
            'item' => $item,
            'initialItem' => $initialItem,
            'shortcuts' => $shortcuts,
            'submitLabel' => $submitLabel,
            'layout' => $layout,
            'showProgress' => $showProgress,
            'progressClass' => $progressClass,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        key($binding->key))
    {{ $slot }}
</{{ $binding->containerTag }}>
