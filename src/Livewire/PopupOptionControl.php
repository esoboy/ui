<?php

namespace Esoboy\Ui\Livewire;

use InvalidArgumentException;
use Livewire\Attributes\Reactive;

abstract class PopupOptionControl extends OptionControl
{
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
}
