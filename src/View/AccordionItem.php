<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class AccordionItem extends Component
{
    public readonly self $accordionItem;

    public function __construct(public readonly bool $disabled = false, public readonly bool $open = false)
    {
        $this->accordionItem = $this;
    }

    public function render(): View
    {
        return view('ui::components.accordion.item');
    }
}
