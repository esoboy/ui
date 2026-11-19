<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;

class Command extends SearchableSelect
{
    public function render(): View
    {
        return view('ui::livewire.command');
    }
}
