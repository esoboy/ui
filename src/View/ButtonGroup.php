<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class ButtonGroup extends Component
{
    // Scoped slots can inherit this group's size without picking up an unrelated ancestor's size.
    public readonly string $buttonGroupSize;

    public function __construct(public readonly string $orientation = 'horizontal', string $size = 'default')
    {
        $this->buttonGroupSize = $size;
    }

    public static function defaultSize(): string
    {
        return app('view')->getConsumableComponentData('buttonGroupSize', 'default');
    }

    public function render(): View
    {
        return view('ui::components.button-group.index');
    }
}
