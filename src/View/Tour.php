<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Tour extends Component
{
    public readonly self $tour;

    /** @var list<int> */
    private array $steps = [];

    private ?string $stepEvent = null;

    public function stepEvent(): string
    {
        return $this->stepEvent ??= 'ui-tour-step-'.hash('xxh128', $this->attributes->get('id') ?? $this->attributes->get('wire:key') ?? ControlAttributes::instanceName('tour-step', $this->attributes->get('ui-site')));
    }

    public function __construct(public readonly bool $initialOpen = false)
    {
        $this->tour = $this;
    }

    public function register(int $index): void
    {
        $this->steps[] = $index;
    }

    /** @return list<int> */
    public function steps(): array
    {
        return $this->steps;
    }

    public function render(): View
    {
        return view('ui::components.tour.index');
    }
}
