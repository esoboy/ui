<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\ChoiceMarkup;
use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Select extends Component
{
    #[Modelable]
    public mixed $value = null;

    #[Reactive]
    public string $markup = '';

    /** @var array<int|string, mixed>|null */
    #[Reactive]
    public ?array $options = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public function choose(string $value): void
    {
        if ($this->settings['disabled'] ?? false) {
            return;
        }

        $choice = collect($this->choices())->firstWhere('value', '===', $value);
        if ($choice === null || ! empty($choice['disabled'])) {
            return;
        }

        $this->value = ($this->settings['number'] ?? false) && is_numeric($value) ? $value + 0 : $value;
        $this->dispatch('ui-model-changed')->self();
    }

    public function render(): View
    {
        $markup = $this->options === null ? new ChoiceMarkup($this->markup) : null;
        $choices = $markup?->options() ?? $this->choices();

        return view('ui::livewire.select', [
            'choiceMarkup' => $markup,
            'choices' => $choices,
            'label' => $this->value === null ? null : collect($choices)->firstWhere('value', '===', (string) $this->value)['label'] ?? null,
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function choices(): array
    {
        return $this->options === null ? (new ChoiceMarkup($this->markup))->options() : Options::normalize($this->options);
    }
}
