<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class Rating extends ValueControl
{
    use HasControlSize;

    #[Reactive]
    public int $max = 5;

    #[Locked]
    public mixed $initialScore = null;

    public function mount(mixed $initialValue = null): void
    {
        parent::mount($initialValue);
        $this->initialScore = $this->current;
    }

    public function resetScore(): void
    {
        $this->changeValue($this->initialScore);
    }

    public function choose(int $score): void
    {
        if ($score >= 1 && $score <= $this->max) {
            $this->changeValue((int) $this->current === $score ? null : $score);
        }
    }

    public function render(): View
    {
        $value = $this->current;
        if ($this->max < 1 || ($value !== null && (filter_var($value, FILTER_VALIDATE_INT) === false || $value < 1 || $value > $this->max))) {
            throw new InvalidArgumentException(__('ui::errors.rating'));
        }

        return view('ui::livewire.rating');
    }
}
