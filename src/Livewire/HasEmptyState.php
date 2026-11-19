<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Attributes\Reactive;

trait HasEmptyState
{
    #[Reactive]
    public ?string $emptyTitle = null;

    #[Reactive]
    public ?string $emptyDescription = null;
}
