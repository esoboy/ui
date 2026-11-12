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

    public string $pageName = 'page';

    /** @var list<int|string> */
    public array $independentSelection = [];

    /** @return array<string, bool> */
    protected function dataTableSelectionProperties(): array
    {
        return ['selected' => true, 'independentSelection' => false];
    }

    protected function dataTablePageName(): string
    {
        return $this->pageName;
    }

    /** @return non-empty-list<int> */
    public function perPageOptions(): array
    {
        return [10, 15, 20, 25, 30, 50];
    }

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
     * @return LengthAwarePaginator<int, array{id: int, email: string, amount: int}>
     */
    public function rows(): LengthAwarePaginator
    {
        $name = $this->dataTablePageName();

        return new LengthAwarePaginator([
            ['id' => 1, 'email' => 'ada@example.test', 'amount' => 12],
            ['id' => 2, 'email' => 'bea@example.test', 'amount' => 23],
        ], 42, $this->perPage, $this->getPage($name), ['pageName' => $name]);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @php($paginator = $this->rows())
                @php($columns = ['email' => ['title' => 'Email', 'sortable' => true], 'amount' => ['title' => 'Amount'], 'status' => ['title' => 'Status', 'fixed' => 'end']])
                <x-ui::data-table selectable>
                    <x-ui::data-table.toolbar :clear-filters="$this->filtered()">
                        <x-slot:primary>
                            <x-ui::data-table.faceted-filter column="status" title="Status" :options="['paid' => 'Paid', 'failed' => ['label' => 'Failed', 'icon' => 'x']]" :counts="['paid' => 3]" />
                        </x-slot:primary>
                    </x-ui::data-table.toolbar>
                    <x-ui::data-table.view-options :columns="$columns" />
                    <x-ui::data-table.table :rows="$paginator" :columns="$columns">
                        @foreach ($component->entries() as $entry)
                            <x-ui::data-table.row :entry="$entry" />
                        @endforeach
                        <x-slot:summary>
                            <x-ui::data-table.summary-row :values="['email' => 'Page subtotal', 'amount' => 35]" />
                        </x-slot:summary>
                    </x-ui::data-table.table>
                    <x-ui::data-table.total :total="$paginator->total()" />
                    <x-ui::data-table.page-size :options="$this->perPageOptions()" />
                    <x-ui::data-table.pagination :paginator="$paginator" />
                    <x-ui::data-table.selection-toolbar>Payment actions</x-ui::data-table.selection-toolbar>
                </x-ui::data-table>
            </div>
            BLADE;
    }
}
