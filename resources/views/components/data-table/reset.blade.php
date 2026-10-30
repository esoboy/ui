@blaze

@use('Esoboy\Ui\Support\Model')

{{-- The toolbar's Reset button of shadcn's tasks example: shown while WithDataTable has filters or a search, it clears them. --}}
@php
$table = Model::component();
@endphp

@if ($table && method_exists($table, 'filtered') && $table->filtered())
<x-ui::button variant="ghost" size="sm" :attributes="$attributes->merge(['wire:click' => 'resetFilters', 'data-data-table-reset' => true])">
    {{ __('ui::data-table.reset') }}
    <x-ui::icon name="x" />
</x-ui::button>
@endif
