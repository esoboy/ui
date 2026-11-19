<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\DataTableColumns;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;
use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;
use InvalidArgumentException;
use Livewire\Livewire;
use LogicException;

final class DataTable extends Component
{
    // Blade exposes this instance to cell and toolbar slots before rendering the table itself.
    public readonly self $dataTable;

    public readonly DataTableColumns $layout;

    /** @var Paginator<array-key, mixed>|CursorPaginator<array-key, mixed>|null */
    public readonly Paginator|CursorPaginator|null $paginator;

    /** @var array<array-key, mixed> */
    public array $records = [];

    /** @var array<array-key, string> */
    public array $selectableKeys = [];

    /** @var array<array-key, array<string, ComponentSlot>> */
    private array $cells = [];

    /**
     * @param  iterable<array-key, mixed>  $rows
     * @param  array<array-key, mixed>  $columns
     * @param  (callable(mixed): bool)|null  $rowSelectable
     */
    public function __construct(
        iterable $rows,
        array $columns,
        public readonly ?string $sortModel = null,
        public readonly ?string $layoutModel = null,
        private readonly ?string $rowKey = null,
        ?callable $rowSelectable = null,
    ) {
        $this->dataTable = $this;
        $this->paginator = $rows instanceof Paginator || $rows instanceof CursorPaginator ? $rows : null;
        $layout = $this->boundValue($layoutModel);
        $this->layout = new DataTableColumns($columns, is_array($layout) ? $layout : []);
        foreach ($rows as $row) {
            $key = $this->keyFor($row);
            if (array_key_exists($key, $this->records)) {
                throw new InvalidArgumentException(__('ui::errors.table_duplicate_key', ['key' => $key]));
            }
            $this->records[$key] = $row;
            if (! $rowSelectable || $rowSelectable($row)) {
                $this->selectableKeys[$key] = $key;
            }
        }
    }

    public static function enclosing(): self
    {
        return app('view')->getConsumableComponentData('dataTable')
            ?? throw new LogicException(__('ui::errors.table_parent'));
    }

    public function keyFor(mixed $row): string
    {
        $key = $this->rowKey === null && $row instanceof Model ? $row->getKey() : data_get($row, $this->rowKey ?? 'id');
        if ((! is_int($key) && ! is_string($key)) || $key === '') {
            throw new InvalidArgumentException(__('ui::errors.table_row_key'));
        }

        return (string) $key;
    }

    public function boundValue(?string $model): mixed
    {
        return $model === null ? null : data_get(Livewire::current(), $model);
    }

    public function selectionModel(): ?string
    {
        return $this->attributes->wire('model')->value() ?: null;
    }

    /** @return array<array-key, true> */
    public function selectedKeys(): array
    {
        return array_fill_keys($this->boundValue($this->selectionModel()) ?? [], true);
    }

    public function rememberCell(string $row, string $column, ComponentSlot $content): void
    {
        $this->cells[$row][$column] = $content;
    }

    public function cell(string $row, string $column): ?ComponentSlot
    {
        return $this->cells[$row][$column] ?? null;
    }

    public function rowLabel(mixed $row, string $key): string
    {
        foreach ($this->layout->visible as $column) {
            if ($column['field'] !== null) {
                $value = data_get($row, $column['field']);
                if (is_scalar($value) && (string) $value !== '') {
                    return (string) $value;
                }
            }
        }

        return $key;
    }

    public function setValue(string $model, mixed $value): string
    {
        return '$set('.Js::from($model).', '.Js::from($value).')';
    }

    public function render(): View
    {
        return view('ui::components.data-table.index');
    }
}
