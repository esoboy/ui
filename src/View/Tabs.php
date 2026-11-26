<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;
use Livewire\Livewire;

final class Tabs extends Component
{
    public readonly self $tabsScope;

    public readonly string $tabsOrientation;

    /** @var list<mixed> */
    private array $enabled = [];

    /** @var array<string, array<string, string>> */
    private array $ids = [];

    private ?string $instance = null;

    /** @var array<int, list<array<string, mixed>>> */
    private array $parts = [];

    public function __construct(string $orientation = 'horizontal', public readonly mixed $initialValue = null)
    {
        $this->tabsOrientation = $orientation;
        $this->tabsScope = $this;
    }

    public function idFor(string $part, mixed $value): string
    {
        $key = serialize($value);

        return $this->ids[$part][$key] ??= ($this->attributes->get('id') ?? $this->groupId()).'-'.$part.'-'.hash('xxh3', $key);
    }

    private function groupId(): string
    {
        return $this->instance ??= ControlAttributes::instanceName('tabs', $this->attributes->get('ui-site'));
    }

    /** @param array<string, mixed> $options */
    public function collect(string $part, mixed $value, ComponentAttributeBag $attributes, ComponentSlot $slot, array $options): void
    {
        // At the end of a Blade component its parent's slot buffer is active.
        // Keep the authored markup preceding this part, including ordinary wrappers.
        $level = ob_get_level();
        $prefix = ob_get_contents();
        ob_clean();
        if ($part === 'list') {
            $children = $this->take($level + 1, $slot);
        } else {
            if ($attributes->has('id')) {
                $this->ids[$part][serialize($value)] = $attributes->get('id');
            }
            if ($part === 'tab' && ! $attributes->get('disabled')) {
                $this->enabled[] = $value;
            }
        }
        $this->parts[$level][] = compact('prefix', 'part', 'value', 'attributes', 'slot', 'options') + ['children' => $children ?? []];
    }

    /** @return list<array<string, mixed>> */
    private function take(?int $level, ComponentSlot $tail): array
    {
        $parts = $this->parts[$level] ?? [];
        unset($this->parts[$level]);
        $parts[] = ['prefix' => $tail->toHtml()];

        return $parts;
    }

    public function content(ComponentSlot $tail): HtmlString
    {
        $parts = $this->take(array_key_first($this->parts), $tail);

        return new HtmlString(view('ui::partials.tabs-parts', ['parts' => $parts, 'scope' => $this])->render());
    }

    public function fieldId(): string
    {
        return 'ui-field-'.$this->groupId();
    }

    /** @return list<mixed> */
    public function values(): array
    {
        return $this->enabled;
    }

    public function selection(): mixed
    {
        $model = $this->attributes->wire('model')->value();
        $value = $model ? data_get(Livewire::current(), ControlAttributes::modelPath($model)) : $this->attributes->get('value', $this->initialValue);

        return in_array($value, $this->enabled, true) || $this->attributes->has('value') ? $value : ($this->enabled[0] ?? null);
    }

    public function render(): View
    {
        return view('ui::components.tabs.index');
    }
}
