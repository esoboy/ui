<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;

class TreeSelect extends Tree
{
    public function render(): View
    {
        $data = $this->viewData();

        return view('ui::livewire.tree-select', [
            ...$data,
            'labels' => Options::find($data['nodes'], (array) $this->value),
        ]);
    }
}
