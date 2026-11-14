<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\DataTableEntry;
use Esoboy\Ui\Support\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\View\Component;

final class DataTable extends Component
{
    /** @var array{columns: array<string, array{title: string, sortable?: bool, fixed?: string}>, actions: bool, selectable: bool, selected: list<string>, details: bool, span: int, tree: bool} */
    public readonly array $tableLayout;

    /**
     * @param  iterable<array-key, array<array-key, mixed>|object>  $rows
     * @param  array<string, array{title: string, sortable?: bool, fixed?: string}>  $columns
     */
    public function __construct(
        private readonly iterable $rows,
        array $columns,
        public readonly bool $actions = false,
        public readonly ?string $actionsTitle = null,
        public readonly bool $stickyHeader = false,
        public readonly bool $details = false,
        private readonly bool $tree = false,
        private readonly string $childrenField = 'children',
    ) {
        $columns = self::displayedColumns($columns);
        $view = app('view');
        $selectable = (bool) $view->getConsumableComponentData('selectable', false);
        $selection = $view->getConsumableComponentData('selection', 'selected');

        $this->tableLayout = [
            'columns' => $columns,
            'actions' => $actions,
            'selectable' => $selectable,
            'selected' => $selectable ? array_values(array_map('strval', data_get(Model::component(), $selection, []))) : [],
            'details' => $details,
            'tree' => $tree,
            'span' => max(1, count($columns) + (int) $selectable + (int) $details + (int) $actions),
        ];
    }

    /**
     * @template TColumn of array{title: string, sortable?: bool, fixed?: string}
     *
     * @param  array<string, TColumn>  $columns
     * @return array<string, TColumn>
     */
    public static function movableColumns(array $columns): array
    {
        return array_filter($columns, fn (array $column) => ! in_array($column['fixed'] ?? null, ['start', 'end'], true));
    }

    /**
     * @template TColumn of array{title: string, sortable?: bool, fixed?: string}
     *
     * @param  array<string, TColumn>  $columns
     * @return array<string, TColumn>
     */
    public static function displayedColumns(array $columns): array
    {
        $owner = Model::component();
        $movable = self::movableColumns($columns);
        $fixed = array_diff_key($columns, $movable);
        $start = array_filter($fixed, fn (array $column) => ($column['fixed'] ?? null) === 'start');
        $end = array_diff_key($fixed, $start);
        $visible = $owner && method_exists($owner, 'visibleColumns') ? $owner->visibleColumns($movable) : $movable;

        return ($start + $visible + $end) ?: array_slice($columns, 0, 1, preserve_keys: true);
    }

    /** @return iterable<DataTableEntry> */
    public function entries(): iterable
    {
        yield from $this->walk($this->rows);
    }

    /**
     * @param  iterable<array-key, array<array-key, mixed>|object>  $rows
     * @return iterable<DataTableEntry>
     */
    private function walk(iterable $rows, ?string $parent = null, int $depth = 0): iterable
    {
        foreach ($rows as $index => $row) {
            $fallback = $parent === null ? (string) $index : $parent.'/'.$index;
            $id = data_get($row, 'id', $fallback);
            $id = is_int($id) || is_string($id) ? (string) $id : $fallback;
            $children = $this->tree ? $this->children($row) : [];

            yield new DataTableEntry($row, $id, $parent, $depth, $children !== []);

            yield from $this->walk($children, $id, $depth + 1);
        }
    }

    /**
     * @param  array<array-key, mixed>|object  $row
     * @return array<array-key, array<array-key, mixed>|object>
     */
    private function children(array|object $row): array
    {
        $field = $this->childrenField;
        if ($row instanceof EloquentModel && $row->isRelation($field)) {
            $children = $row->relationLoaded($field) ? $row->getRelation($field) : null;
        } else {
            $children = data_get($row, $field);
        }

        return is_iterable($children) ? array_filter(iterator_to_array($children), fn ($child) => is_array($child) || is_object($child)) : [];
    }

    public function render(): View
    {
        return view('ui::components.data-table.table');
    }
}
