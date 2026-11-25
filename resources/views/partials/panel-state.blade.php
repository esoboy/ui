@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, $kind);
    $parameters = $binding->parameters;
    $parameters['initialValue'] = $initial;
    $__slots = ['default' => $slot->toHtml()];
@endphp
@livewire('ui::panel-state', [...$parameters, 'kind' => $kind, 'settings' => $settings], key($binding->key), $__slots ?? [])
