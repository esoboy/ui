<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Confirm extends Component
{
    #[Locked]
    public string $panel = '';

    #[Locked]
    public string $question = '';

    #[Locked]
    public string $label = '';

    #[Locked]
    public string $description = '';

    public function prepare(string $panel, string $question, string $label, string $description = ''): void
    {
        $this->panel = $panel;
        $this->question = $question;
        $this->label = $label;
        $this->description = $description;
    }

    public function render(): View
    {
        return view('ui::livewire.confirm');
    }
}
