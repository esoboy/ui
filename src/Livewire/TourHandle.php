<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Component;

/**
 * Ui::tour('onboarding')->start() / ->close().
 */
final class TourHandle
{
    public function __construct(
        private readonly Component $component,
        private readonly string $name,
    ) {}

    /**
     * Starts the tour, at its first step or the one given (from 0).
     */
    public function start(int $step = 0): void
    {
        $this->component->dispatch('ui-tour', name: $this->name, open: true, step: $step);
    }

    public function close(): void
    {
        $this->component->dispatch('ui-tour', name: $this->name, open: false, step: null);
    }
}
