<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Slider extends Component
{
    use ForwardsControlAttributes;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    #[Locked]
    public mixed $defaultValue = null;

    public function render(): View
    {
        return view('ui::partials.slider', [
            'config' => $this->settings + ['value' => $this->defaultValue],
            'inputId' => $this->fieldAttributes['id'] ?? null,
            'controlKey' => null,
        ]);
    }
}
