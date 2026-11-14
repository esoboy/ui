<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class InputCount extends Component
{
    #[Reactive]
    public string $initial = '';

    #[Reactive]
    public ?int $max = null;

    public ?string $draft = null;

    public function countText(string $text): void
    {
        $this->draft = $text;
    }

    public function updatedInitial(): void
    {
        $this->draft = null;
    }

    public function render(): View
    {
        $text = $this->draft ?? $this->initial;

        return view('ui::livewire.input-count', ['count' => mb_strlen($text)]);
    }
}
