<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;

class TreeSelect extends Tree
{
    public function render(): View
    {
        return view('ui::livewire.tree-select', [
            'nodes' => $this->data(),
            'labels' => Options::find($this->data(), (array) $this->value),
        ]);
    }
}
