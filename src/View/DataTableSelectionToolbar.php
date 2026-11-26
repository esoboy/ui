<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class DataTableSelectionToolbar extends Component
{
    public readonly DataTable $dataTable;

    public readonly int $count;

    public function __construct(public readonly string $placement = 'table', public readonly bool $reactiveCount = false)
    {
        $this->dataTable = DataTable::enclosing();
        $this->count = count($this->dataTable->boundValue($this->dataTable->selectionModel()) ?? []);
    }

    public function shouldRender(): bool
    {
        return $this->reactiveCount || $this->count > 0;
    }

    public function render(): View
    {
        return view('ui::components.data-table.selection-toolbar');
    }
}
