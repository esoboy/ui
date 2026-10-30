<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Esoboy\Ui\Livewire\WithDataTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

/**
 * A Data Table over a fixed page of payments.
 */
class Payments extends Component
{
    use WithDataTable;

    public string $searchedFor = '';

    // The app's own hook for a property the trait resets the page on.
    public function updatedSearch(string $search): void
    {
        $this->searchedFor = $search;
    }

    public function sortedSql(): string
    {
        return $this->sortQuery(app('db')->table('payments'), ['email'])->toSql();
    }

    /**
     * @return list<int>
     */
    public function filteredIds(): array
    {
        return array_map(fn ($id) => (int) $id, array_values($this->filterQuery(app('db')->table('payments'), ['status', 'method'])->orderBy('id')->pluck('id')->all()));
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        return $this->facetCounts(app('db')->table('payments')->orderBy('id'), 'status', ['status', 'method']);
    }

    /**
     * @return LengthAwarePaginator<int, array{id: int}>
     */
    public function rows(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([['id' => 1], ['id' => 2]], 42, $this->perPage, $this->getPage());
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @php($paginator = $this->rows())
                <x-ui::data-table>
                    <x-ui::data-table.toolbar>
                        <x-ui::data-table.faceted-filter column="status" title="Status" :options="['paid' => 'Paid', 'failed' => ['label' => 'Failed', 'icon' => 'x']]" :counts="['paid' => 3]" />
                        <x-ui::data-table.reset />
                        <x-ui::data-table.view-options :columns="['email' => 'Email', 'amount' => 'Amount']" reorderable />
                    </x-ui::data-table.toolbar>
                    <x-ui::data-table.content>
                        <x-ui::table.header>
                            <x-ui::table.row>
                                <x-ui::table.head><x-ui::data-table.select-all /></x-ui::table.head>
                                <x-ui::table.head><x-ui::data-table.column-header column="email" title="Email" /></x-ui::table.head>
                                <x-ui::table.head><x-ui::data-table.column-header column="status" title="Status" :sortable="false" /></x-ui::table.head>
                                <x-ui::table.head><x-ui::data-table.column-header column="method" title="Method" :sortable="false" :hideable="false" :options="['card' => 'Card', 'bank' => 'Bank']" /></x-ui::table.head>
                            </x-ui::table.row>
                        </x-ui::table.header>
                        <x-ui::table.body>
                            @foreach ($paginator as $row)
                            <x-ui::table.row><x-ui::table.cell><x-ui::data-table.select-row :value="$row['id']" /></x-ui::table.cell></x-ui::table.row>
                            @endforeach
                            <x-ui::data-table.empty colspan="3" />
                        </x-ui::table.body>
                    </x-ui::data-table.content>
                    <x-ui::data-table.pagination :paginator="$paginator" />
                </x-ui::data-table>
            </div>
            BLADE;
    }
}
