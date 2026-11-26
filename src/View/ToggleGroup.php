<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class ToggleGroup extends Component
{
    public readonly self $toggleGroup;

    public readonly string $groupName;

    public function __construct(public readonly string $type = 'single', public readonly string $variant = 'default',
        public readonly string $size = 'default', public readonly bool $disabled = false,
        public readonly string $orientation = 'horizontal', public readonly int|float|string $spacing = 2, ?string $uiSite = null)
    {
        $this->toggleGroup = $this;
        $this->groupName = ControlAttributes::instanceName('toggle-group', $uiSite);
    }

    public function render(): View
    {
        return view('ui::components.toggle-group.index');
    }
}
