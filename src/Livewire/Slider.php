<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Slider extends Component
{
    use ForwardsControlAttributes;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public mixed $value = null;

    public function mount(mixed $defaultValue = null): void
    {
        $this->value = $defaultValue ?? $this->settings['min'];
    }

    public function render(): View
    {
        return view('ui::partials.slider', [
            'config' => array_merge($this->settings, ['value' => $this->value, 'model' => 'value']),
            'inputId' => $this->fieldAttributes['id'] ?? null,
            'controlKey' => null,
        ]);
    }
}
