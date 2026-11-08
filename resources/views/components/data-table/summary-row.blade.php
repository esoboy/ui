{{-- Consume the layout of the enclosing class component. --}}
@aware(['tableLayout'])
@props(['values'])

<x-ui::table.row {{ $attributes->merge(['data-slot' => 'data-table-summary-row']) }}>
    @if ($tableLayout['selectable'])
        <x-ui::table.cell sticky="start" />
    @endif
    @if ($tableLayout['details'])
        <x-ui::table.cell />
    @endif
    @foreach ($tableLayout['columns'] as $key => $column)
        <x-ui::table.cell :sticky="$column['fixed'] ?? null">{{ $values[$key] ?? '' }}</x-ui::table.cell>
    @endforeach
    @if ($tableLayout['actions'])
        <x-ui::table.cell />
    @endif
</x-ui::table.row>
