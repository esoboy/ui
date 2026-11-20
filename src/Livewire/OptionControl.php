<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

abstract class OptionControl extends ChoiceControl
{
    /** @var list<array{value: string|int|null, label: string, disabled?: bool}> */
    #[Reactive]
    public array $options = [];

    #[Reactive]
    public bool $multiple = false;

    #[Reactive]
    public string $placeholder = '';

    protected function multipleSelection(): bool
    {
        return $this->multiple;
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function choices(): array
    {
        return Options::validate($this->options, allowNull: ! $this->multiple);
    }
}
