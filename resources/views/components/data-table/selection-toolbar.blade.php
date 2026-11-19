<div {{ $attributes->merge(['data-slot' => 'data-table-selection', 'role' => 'group', 'aria-label' => __('ui::ui.table_selection')]) }}
    data-placement="{{ $placement }}">
    <span role="status">{{ __('ui::ui.selected_count', ['count' => $count]) }}</span>
    @if ($slot->isNotEmpty())
        <x-ui::separator orientation="vertical" />
        <div data-slot="data-table-selection-actions">{{ $slot }}</div>
    @endif
    <x-ui::button variant="ghost" size="icon-sm" wire:click="{{ $dataTable->setValue($dataTable->selectionModel(), []) }}"
        :aria-label="__('ui::ui.table_clear_selection')" :title="__('ui::ui.table_clear_selection')"><x-ui::icon name="x" /></x-ui::button>
</div>
