<div {{ $attributes->merge(['class' => 'ui-data-table-selection-toolbar ui-data-table-selection', 'role' => 'group', 'aria-label' => __('ui::ui.table_selection')]) }}
    wire:show="($get(@js($dataTable->selectionModel())) ?? []).length > 0" @if ($count === 0) style="display:none" @endif data-placement="{{ $placement }}">
    <span role="status" wire:text="@js(__('ui::ui.selected_count', ['count' => '__COUNT__'])).replace('__COUNT__', ($get(@js($dataTable->selectionModel())) ?? []).length)">{{ __('ui::ui.selected_count', ['count' => $count]) }}</span>
    @if ($slot->isNotEmpty())
        <x-ui::separator orientation="vertical" />
        <div class="ui-data-table-selection-actions">{{ $slot }}</div>
    @endif
    <x-ui::button variant="ghost" size="icon-sm" x-on:click="selectRows(null)" x-on:blur="forward('blur')" x-on:keydown.enter="forward('keydown')"
        :aria-label="__('ui::ui.table_clear_selection')" :title="__('ui::ui.table_clear_selection')"><x-ui::icon name="x" /></x-ui::button>
</div>
