<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Attributes\Reactive;

trait HasControlSize
{
    #[Reactive]
    public string $size = 'default';
}
