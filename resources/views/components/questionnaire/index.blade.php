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
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'questionnaire', get_defined_vars()))
<div data-slot="questionnaire-field">
    {!! \Livewire\Livewire::mount(
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
        $binding->key,
    ) !!}
    {{ $slot }}
</div>
