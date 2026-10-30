<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Server state for a Data Table, the Livewire counterpart of shadcn's TanStack table
 * features: sorting, filtering, page size, row selection, and the columns' visibility
 * and order (kept in the session, so they are there when the user comes back). The
 * Data Table parts read and change it; the query stays yours.
 */
trait WithDataTable
{
    use WithPagination;

    #[Url(except: null)]
    public ?string $sortBy = null;

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    #[Url(except: 10)]
    public int $perPage = 10;

    /**
     * The values each column is filtered to, as ['status' => ['todo', 'done']].
     *
     * @var array<string, list<string>>
     */
    #[Url(except: [])]
    public array $filters = [];

    #[Url(except: '')]
    public string $search = '';

    /** @var list<int|string> */
    public array $selected = [];

    /** @var list<string> */
    #[Session]
    public array $hiddenColumns = [];

    /**
     * The columns in the order the user put them; empty while they are as written.
     *
     * @var list<string>
     */
    #[Session]
    public array $columnOrder = [];

    /**
     * Sorts by a column: in the given direction, or ascending then descending.
     */
    public function sort(string $column, ?string $direction = null): void
    {
        $this->sortDirection = $direction ?? ($this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc');
        $this->sortBy = $column;
        $this->resetPage();
    }

    public function toggleColumn(string $column): void
    {
        $this->hiddenColumns = in_array($column, $this->hiddenColumns, true)
            ? array_values(array_diff($this->hiddenColumns, [$column]))
            : [...$this->hiddenColumns, $column];
    }

    public function columnVisible(string $column): bool
    {
        return ! in_array($column, $this->hiddenColumns, true);
    }

    /**
     * Moves a column to a position among the columns (their keys as written, or those
     * keys comma-separated, as View Options' wire:sort sends them).
     *
     * @param  list<string>|string  $columns
     */
    public function moveColumn(string $column, int $position, array|string $columns): void
    {
        $columns = is_string($columns) ? explode(',', $columns) : $columns;
        $order = array_values(array_diff(array_keys($this->orderedColumns(array_fill_keys($columns, ''))), [$column]));

        array_splice($order, max(0, min($position, count($order))), 0, [$column]);

        $this->columnOrder = $order;
    }

    /**
     * Shows every column again, in the order written.
     */
    public function resetColumns(): void
    {
        $this->columnOrder = [];
        $this->hiddenColumns = [];
    }

    /**
     * The columns (key => label, in the order written) in the user's order; columns
     * the order doesn't know yet keep their place after the ones it does.
     *
     * @template TLabel
     *
     * @param  array<string, TLabel>  $columns
     * @return array<string, TLabel>
     */
    public function orderedColumns(array $columns): array
    {
        $known = array_values(array_intersect($this->columnOrder, array_keys($columns)));
        $keys = [...$known, ...array_values(array_diff(array_keys($columns), $known))];

        return array_combine($keys, array_map(fn (string $key) => $columns[$key], $keys));
    }

    /**
     * The columns the user shows, in the user's order: what a table renders.
     *
     * @template TLabel
     *
     * @param  array<string, TLabel>  $columns
     * @return array<string, TLabel>
     */
    public function visibleColumns(array $columns): array
    {
        return array_filter($this->orderedColumns($columns), fn (string $key) => $this->columnVisible($key), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Adds a value to a column's filter, or takes it out.
     */
    public function toggleFilter(string $column, string $value): void
    {
        $values = $this->filters[$column] ?? [];

        $this->filters[$column] = in_array($value, $values, true) ? array_values(array_diff($values, [$value])) : [...$values, $value];

        if ($this->filters[$column] === []) {
            unset($this->filters[$column]);
        }

        $this->resetPage();
    }

    /**
     * Clears one column's filter, or all of them.
     */
    public function clearFilters(?string $column = null): void
    {
        if ($column === null) {
            $this->filters = [];
        } else {
            unset($this->filters[$column]);
        }

        $this->resetPage();
    }

    /**
     * Clears the filters and the search, like the Reset button of shadcn's toolbar.
     */
    public function resetFilters(): void
    {
        $this->filters = [];
        $this->search = '';
        $this->resetPage();
    }

    public function filtered(): bool
    {
        return $this->filters !== [] || $this->search !== '';
    }

    /**
     * The page sizes Pagination offers. $perPage is always one of them: another (from
     * the URL, say) becomes the first.
     *
     * @return non-empty-list<int>
     */
    public function perPageOptions(): array
    {
        return [10, 20, 25, 30, 40, 50];
    }

    public function mountWithDataTable(): void
    {
        $this->keepDataTableInBounds();
    }

    /**
     * A new page size, filter or search starts again from the first page. Livewire's
     * hook for this trait, so a component's own updatedSearch() and the like add to it.
     */
    public function updatedWithDataTable(string $property): void
    {
        if (in_array(strtok($property, '.'), ['perPage', 'filters', 'search'], true)) {
            $this->keepDataTableInBounds();
            $this->resetPage();
        }
    }

    /**
     * Keeps what the URL or the browser sent to what the table takes: a page size it
     * offers, and filters that are lists of values.
     */
    private function keepDataTableInBounds(): void
    {
        if (! in_array($this->perPage, $this->perPageOptions(), true)) {
            $this->perPage = $this->perPageOptions()[0];
        }

        $this->filters = self::filterValues($this->filters);
    }

    /**
     * @param  array<mixed>  $filters
     * @return array<string, list<string>>
     */
    private static function filterValues(array $filters): array
    {
        $kept = [];

        foreach ($filters as $column => $values) {
            $values = is_string($column) && is_array($values) ? array_values(array_filter($values, is_string(...))) : [];

            if ($values !== []) {
                $kept[$column] = $values;
            }
        }

        return $kept;
    }

    /**
     * Applies the filters of the filterable columns, each to its values.
     *
     * @template TQuery of Builder
     *
     * @param  TQuery  $query
     * @param  list<string>  $filterable
     * @return TQuery
     */
    protected function filterQuery(Builder $query, array $filterable, ?string $except = null): Builder
    {
        foreach ($this->filters as $column => $values) {
            if ($column !== $except && $values !== [] && in_array($column, $filterable, true)) {
                $query->whereIn($column, $values);
            }
        }

        return $query;
    }

    /**
     * How many rows have each value of a column, under the other columns' filters
     * (TanStack's faceted unique values), as ['todo' => 12, 'done' => 3].
     *
     * @param  list<string>  $filterable
     * @return array<string, int>
     */
    protected function facetCounts(Builder $query, string $column, array $filterable): array
    {
        $counts = $this->filterQuery(clone $query, $filterable, except: $column)
            ->reorder()
            ->select($column)
            ->selectRaw('count(*) as aggregate')
            ->groupBy($column)
            ->pluck('aggregate', $column);

        return collect($counts)->mapWithKeys(fn ($count, $value) => [(string) $value => (int) $count])->all();
    }

    /**
     * Applies the current sort, if it is one of the sortable columns.
     *
     * @template TQuery of Builder
     *
     * @param  TQuery  $query
     * @param  list<string>  $sortable
     * @return TQuery
     */
    protected function sortQuery(Builder $query, array $sortable): Builder
    {
        if ($this->sortBy !== null && in_array($this->sortBy, $sortable, true)) {
            $query->orderBy($this->sortBy, $this->sortDirection === 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }
}
