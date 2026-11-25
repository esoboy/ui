<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Reactive;

trait ForwardsControlAttributes
{
    /** @var array<string, mixed>|null Attributes forwarded by the parent Blade component. */
    #[Reactive]
    public ?array $fieldAttributes = null;

    public function renderingForwardsControlAttributes(View $view): void
    {
        if ($this->fieldAttributes !== null) {
            $view->with('attributes', new ComponentAttributeBag($this->fieldAttributes));
        }
    }
}
