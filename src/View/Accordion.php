<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Accordion extends Component
{
    public readonly self $accordion;

    public readonly string $groupName;

    public function __construct(public readonly bool $multiple = false, public readonly bool $collapsible = true)
    {
        $this->accordion = $this;
        $this->groupName = ControlAttributes::instanceName('accordion');
    }

    public function render(): View
    {
        return view('ui::components.accordion.index');
    }
}
