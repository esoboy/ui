@aware(['dataTable'])
@if ($dataTable->layoutModel)
    @livewire('ui::data-table-settings', [
        'columns' => $dataTable->columns,
        'wire:model.live' => $dataTable->layoutModel,
        ...$attributes->all(),
    ], key('column-settings:'.($attributes->get('wire:key') ?? $dataTable->layoutModel)))

@endif
