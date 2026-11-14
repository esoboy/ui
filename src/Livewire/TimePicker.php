<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\TimeOptions;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TimePicker extends Component
{
    use SyncsModel;

    #[Modelable]
    public mixed $value = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    #[Locked]
    public ?int $shownHour = null;

    public function showTimeHour(int $hour): void
    {
        if ($hour >= 0 && $hour < 24) {
            $this->shownHour = $hour;
        }
    }

    protected function modelChanged(): void
    {
        $this->shownHour = null;
    }

    public function pickTime(string $unit, int $option): void
    {
        if (($this->settings['disabled'] ?? false) || ($this->settings['readonly'] ?? false)) {
            return;
        }

        $choice = $this->options()->choose($this->value, $unit, $option);
        if ($choice !== null) {
            $this->value = $choice;
            if ($unit === 'combined') {
                $this->shownHour = null;
                $this->dispatch('ui-time-picked');
            }
        }
    }

    private function options(): TimeOptions
    {
        return new TimeOptions(
            $this->settings['step'] ?? null,
            $this->settings['min'] ?? null,
            $this->settings['max'] ?? null,
            combined: ($this->settings['mode'] ?? null) === 'combined',
        );
    }

    public function render(): View
    {
        $label = TimeOptions::parse($this->value) === null ? '' : $this->value;

        return view('ui::livewire.time-picker', ['label' => $label, 'timeOptions' => $this->options()]);
    }
}
