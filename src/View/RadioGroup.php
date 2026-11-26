<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class RadioGroup extends Component
{
    public readonly self $radioGroup;

    public readonly string $groupName;

    public function __construct(public readonly string $size = 'default', ?string $uiSite = null)
    {
        $this->radioGroup = $this;
        $this->groupName = ControlAttributes::instanceName('radio-group', $uiSite);
    }

    public function render(): View
    {
        $menu = str_contains($this->componentName, '.') ? str_replace(['ui::', '.radio-group'], '', $this->componentName) : null;

        return view('ui::components.radio-group.index', compact('menu'));
    }
}
