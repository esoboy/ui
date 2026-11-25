<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Tour extends Component
{
    public readonly self $tour;

    /** @var list<int> */
    private array $steps = [];

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
