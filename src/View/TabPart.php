<?php

namespace Esoboy\Ui\View;

use Closure;
use Illuminate\View\Component;

final class TabPart extends Component
{
    public function __construct(
        public readonly mixed $value = null,
        public readonly string $size = 'default',
        public readonly string $variant = 'default',
        public readonly bool $activateOnFocus = false,
    ) {}

    public function render(): Closure
    {
        $scope = app('view')->getConsumableComponentData('tabsScope');

        return function (array $data) use ($scope): string {
            $part = match ($this->componentName) {
                'ui::tabs.list' => 'list',
                'ui::tabs.content' => 'panel',
                default => 'tab',
            };
            $scope->collect($part, $this->value, $data['attributes'], $data['slot'], [
                'variant' => $this->variant, 'size' => $this->size, 'activateOnFocus' => $this->activateOnFocus,
            ]);

            return '';
        };
    }
}
