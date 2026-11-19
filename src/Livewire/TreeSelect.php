<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;

class TreeSelect extends Tree
{
    #[Reactive]
    public string $placeholder = '';

    public function render(): View
    {
        return view('ui::livewire.tree-select');
    }
}
