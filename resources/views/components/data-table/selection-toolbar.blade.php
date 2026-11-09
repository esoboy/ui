@blaze

@use('Esoboy\Ui\Support\Model')
@use('Illuminate\Support\Js')

@aware(['selectable' => false, 'selection' => 'selected'])

@php($selected = $selectable ? data_get(Model::component(), $selection, []) : [])

@if ($selected)
<div {{ $attributes->merge(['data-slot' => 'data-table-selection-toolbar', 'role' => 'toolbar', 'aria-label' => __('ui::data-table.selection')]) }}>
    <div data-slot="data-table-selection-status">
        <span data-slot="data-table-selection-count" aria-live="polite">{{ __('ui::data-table.selected_count', ['count' => count($selected)]) }}</span>
        <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::data-table.clear_selection')" :wire:click="'clearSelection('.Js::from($selection).')'">
            <x-ui::icon name="x" />
        </x-ui::button>
    </div>
    @if ($slot->isNotEmpty())
        <div data-slot="data-table-selection-actions">{{ $slot }}</div>
    @endif
</div>
@endif
