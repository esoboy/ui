@aware(['dataTable'])
@if ($dataTable->layoutModel)
    @php
        $path = \Esoboy\Ui\Support\ControlAttributes::modelPath($dataTable->layoutModel);
        $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->merge(['wire:model.live' => $path]), 'column-settings');
        $settingsKey = 'column-settings:'.hash('xxh128', serialize([$dataTable->scope(), $attributes->get('wire:key'), $attributes->get('id'), $path]));
    @endphp
    @livewire('ui::data-table-settings', [...$binding->parameters, 'columns' => $dataTable->columns], key($settingsKey))

@endif
