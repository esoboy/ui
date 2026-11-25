<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\DataTableColumns;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class DataTableSettings extends Component
{
    /** @var array<string, mixed>|null */
    #[Modelable]
    public ?array $value = null;

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

    public function reorder(string $key, int $position): void
    {
        $order = $this->movable();
        $index = array_search($key, $order, true);
        if ($index === false || ! isset($order[$position])) {
            return;
        }
        array_splice($order, $index, 1);
        array_splice($order, $position, 0, [$key]);
        $this->value['order'] = $order;
    }

    public function resetLayout(): void
    {
        $this->value = ['order' => [], 'hidden' => []];
    }

    public function render(): View
    {
        return view('ui::livewire.data-table-settings', ['layout' => $this->layout()]);
    }
}
