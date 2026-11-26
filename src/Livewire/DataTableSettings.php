<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\DataTableColumns;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;

class DataTableSettings extends ValueControl
{
    /** @var array<array-key, mixed> */
    #[Reactive]
    public array $columns = [];

    private function layout(): DataTableColumns
    {
        return new DataTableColumns($this->columns, $this->value ?? []);
    }

    public function toggleColumn(string $key): void
    {
        $layout = $this->layout();
        if (! isset($layout->ordered[$key]) || ! $layout->canToggle($key)) {
            return;
        }
        $hidden = array_diff_key($layout->ordered, $layout->visible);
        if (isset($hidden[$key])) {
            unset($hidden[$key]);
        } else {
            $hidden[$key] = true;
        }
        $this->value['hidden'] = array_keys($hidden);
    }

    /** @return list<string> */
    private function movable(): array
    {
        return array_keys(array_filter($this->layout()->ordered, fn ($column) => $column['reorderable']));
    }

    public function move(string $key, int $direction): void
    {
        $order = $this->movable();
        $index = array_search($key, $order, true);
        if ($index !== false && in_array($direction, [-1, 1], true) && isset($order[$index + $direction])) {
            $this->reorder($key, $index + $direction);
            $this->dispatch('ui-column-focus', key: $key)->self();
        }
    }

    /** @return list<string> */
    public function orderAfter(string $key, int $position): array
    {
        $order = $this->movable();
        $index = array_search($key, $order, true);
        if ($index === false || ! isset($order[$position])) {
            return $order;
        }
        array_splice($order, $index, 1);
        array_splice($order, $position, 0, [$key]);

        return $order;
    }

    public function reorder(string $key, int $position): void
    {
        $this->value['order'] = $this->orderAfter($key, $position);
    }

    public function afterEdit(?string $focusColumn): void
    {
        if ($focusColumn !== null && isset($this->layout()->ordered[$focusColumn])) {
            $this->dispatch('ui-column-focus', key: $focusColumn)->self();
        }
    }

    public function resetLayout(): void
    {
        $this->value = ['order' => [], 'hidden' => []];
    }

    public function render(): View
    {
        return view('ui::livewire.data-table-settings', ['layout' => $this->layout(), 'movable' => $this->movable()]);
    }
}
