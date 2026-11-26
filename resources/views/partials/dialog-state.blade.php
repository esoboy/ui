@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, $kind);
    $accepted = $binding->model === null ? $attributes->get('value', $initialOpen) : data_get(\Livewire\Livewire::current(), $binding->model);
@endphp
@if ($binding->parameters['mode'] !== 'internal')
<input type="checkbox" hidden data-dialog-field data-mode="{{ $binding->parameters['mode'] }}"
    @if ($binding->model !== null) {{ $binding->parameters['modelDirective'] }}="{{ $binding->model }}" data-model="{{ $binding->model }}" @endif
    @checked($accepted) @disabled($attributes->get('disabled') || $attributes->get('readonly'))>
@endif
