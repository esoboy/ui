<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class RadioGroup extends Component
{
    public readonly self $radioGroup;

    public readonly string $groupName;

    public function __construct(public readonly string $size = 'default')
    {
        $this->radioGroup = $this;
        $this->groupName = ControlAttributes::instanceName('radio-group');
    }

    public function render(): View
    {
        return view('ui::components.radio-group.index');
    }
}
