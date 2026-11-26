<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
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

    private readonly string $instance;

    public readonly DataTableColumns $layout;

    /** @var Paginator<array-key, mixed>|CursorPaginator<array-key, mixed>|null */
    public readonly Paginator|CursorPaginator|null $paginator;

    /** @var array<array-key, mixed> Current page indexed by row key, available to Blade scoped slots. */
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
        public readonly array $columns,
        public readonly ?string $sortModel = null,
        public readonly ?string $layoutModel = null,
        private readonly ?string $rowKey = null,
        ?callable $rowSelectable = null,
        public readonly string $size = 'default',
        ?string $uiSite = null,
    ) {
        $this->dataTable = $this;
        $this->instance = ControlAttributes::instanceName('table', $uiSite);
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
        return $model === null ? null : data_get(Livewire::current(), ControlAttributes::modelPath($model));
    }

    public function scope(): string
    {
        return (string) ($this->attributes->get('id') ?? $this->attributes->get('wire:key') ?? $this->instance);
    }

    public function selectionModel(): ?string
    {
        return ControlAttributes::modelPath($this->attributes->wire('model')->value());
    }

    /** @return list<string|int|float|bool> */
    public function pageValues(): array
    {
        $modifiers = $this->attributes->wire('model')->modifiers()->takeUntil('live');
        $keys = array_values($this->selectableKeys);
        if ($modifiers->contains('number')) {
            return array_map(static fn ($key) => is_numeric($key) ? $key + 0 : $key, $keys);
        }
        if ($modifiers->contains('boolean')) {
            return array_map(static fn ($key) => ! in_array($key, ['0', 'false', 'off', 'no'], true), $keys);
        }

        return $keys;
    }

    /** @return array<int, array{value: string, page: bool, selected: bool}> */
    public function selectionOptions(): array
    {
        $format = static fn ($value) => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        $page = array_map($format, $this->pageValues());
        $selected = array_map($format, $this->boundValue($this->selectionModel()) ?? []);

        return array_map(static fn ($key) => ['value' => $key, 'page' => in_array($key, $page, true), 'selected' => in_array($key, $selected, true)],
            array_unique([...$selected, ...$page]));
    }

    /** @return array<array-key, true> */
    public function selectedKeys(): array
    {
        $selected = array_map(static fn ($key) => is_bool($key) ? ($key ? 'true' : 'false') : (string) $key, $this->boundValue($this->selectionModel()) ?? []);
        $keys = array_fill_keys($selected, true);
        foreach (array_combine($this->selectableKeys, $this->pageValues()) as $rowKey => $value) {
            if (isset($keys[is_bool($value) ? ($value ? 'true' : 'false') : (string) $value])) {
                $keys[$rowKey] = true;
            }
        }

        return $keys;
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
