<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

class Select extends ChoiceControl
{
    /** @var list<array{value: string|int|null, label: string, disabled?: bool}> */
    #[Reactive]
    public array $options = [];

    #[Reactive]
    public bool $multiple = false;

    #[Reactive]
    public string $placeholder = '';

    #[Reactive]
    public string $size = 'default';

    #[Reactive]
    public ?string $displayValue = null;

    #[Reactive]
    public string $side = 'bottom';

    #[Reactive]
    public string $align = 'start';

    #[Reactive]
    public float $sideOffset = 4;

    #[Reactive]
    public float $alignOffset = 0;

    #[Reactive]
    public bool $alignItemWithTrigger = false;

    #[Reactive]
    public string $popupClass = '';

    public function rendering(): void
    {
        if (! in_array($this->side, ['top', 'right', 'bottom', 'left', 'inline-start', 'inline-end'], true)
            || ! in_array($this->align, ['start', 'center', 'end'], true)
            || ! is_finite($this->sideOffset) || ! is_finite($this->alignOffset)) {
            throw new InvalidArgumentException(__('ui::errors.select_placement'));
        }
    }

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

    public function render(): View
    {
        return view('ui::livewire.select');
    }
}
