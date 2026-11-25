<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Livewire\Livewire;

final class Tabs extends Component
{
    public readonly string $tabsOrientation;

    public readonly self $tabsScope;

    public readonly string $groupId;

    /** @var array<string, bool> */
    private array $enabled = [];

    public function __construct(string $orientation = 'horizontal', public readonly mixed $initialValue = null)
    {
        $this->tabsOrientation = $orientation;
        $this->tabsScope = $this;
        $this->groupId = ControlAttributes::instanceName('tabs');
    }

    public function register(string $value, bool $disabled): void
    {
        if (! $disabled) {
            $this->enabled[$value] = true;
        }
    }

    /** @return list<string> */
    public function values(): array
    {
        return array_map(strval(...), array_keys($this->enabled));
    }

    public function selection(): ?string
    {
        $model = $this->attributes->wire('model')->value();
        $value = $model ? data_get(Livewire::current(), $model) : $this->initialValue;
        if ($value !== null) {
            return (string) $value;
        }

        $first = array_key_first($this->enabled);

        return $first === null ? null : (string) $first;
    }

    public function render(): View
    {
        return view('ui::components.tabs.index');
    }
}
