<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;

class Select extends PopupOptionControl
{
    #[Reactive]
    public string $size = 'default';

    #[Reactive]
    public ?string $displayValue = null;

    public function render(): View
    {
        return view('ui::livewire.select');
    }
}
