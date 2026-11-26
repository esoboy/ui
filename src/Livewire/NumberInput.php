<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Reactive;

class NumberInput extends ValueControl
{
    /** @var array{min: int|float|string|null, max: int|float|string|null, step: int|float|string, size: string} */
    #[Reactive]
    public array $settings = ['min' => null, 'max' => null, 'step' => 1, 'size' => 'default'];

    public function propose(mixed $value): void
    {
        if ($value === '' || $value === null) {
            $this->changeValue(null);

            return;
        }
        if (! is_numeric($value) || ! is_finite((float) $value)) {
            return;
        }
        $number = $value + 0;
        if (($this->settings['min'] !== null && $number < $this->settings['min']) || ($this->settings['max'] !== null && $number > $this->settings['max'])) {
            return;
        }

        $this->changeValue($number);
    }

    public function render(): View
    {
        return view('ui::livewire.number-input', [
            ...$this->settings, 'controlKey' => null, 'id' => $this->fieldAttributes['id'] ?? null,
            'numberValue' => $this->externalValue,
        ]);
    }
}
