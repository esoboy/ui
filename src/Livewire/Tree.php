<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Tree extends Component
{
    #[Modelable]
    public mixed $value = null;

    /** @var list<array<string, mixed>> */
    #[Reactive]
    public array $items = [];

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    /** @var list<string> */
    public array $expanded = [];

    public string $query = '';

    /** @var array<string, list<array<string, mixed>>> */
    #[Locked]
    public array $loaded = [];

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $items = [], array $settings = [], mixed $value = null): void
    {
        $this->items = $items;
        $this->settings = $settings;
        $this->expanded = array_values(array_map('strval', $settings['expanded'] ?? []));
        $this->value = ($settings['checkable'] ?? false) ? $this->cascade((array) $value) : $value;
        if ($settings['expandAll'] ?? false) {
            $this->expanded = array_map('strval', array_keys(array_filter($this->index(), fn ($node) => ! empty($node['children']))));
        }
    }

    /** @return list<array<string, mixed>> */
    public function data(): array
    {
        $items = $this->items;
        foreach ($this->loaded as $value => $children) {
            $items = Options::put($items, $value, $children);
        }

        return $items;
    }

    /** @return array<string, array<string, mixed>> */
    public function index(): array
    {
        $index = [];
        $walk = function (array $items, bool $disabled = false) use (&$walk, &$index): void {
            foreach ($items as $item) {
                $item['value'] = (string) $item['value'];
                $item['disabled'] = $disabled || ! empty($item['disabled']);
                $index[$item['value']] = $item;
                $walk($item['children'] ?? [], $item['disabled']);
            }
        };
        $walk($this->data());

        return $index;
    }

    public function expand(string $value): void
    {
        $item = $this->index()[$value] ?? null;
        if (! $item || ! empty($item['disabled']) || ($this->settings['disabled'] ?? false)) {
            return;
        }
        if (in_array($value, $this->expanded, true)) {
            $this->expanded = array_values(array_diff($this->expanded, [$value]));

            return;
        }
        $this->expanded[] = $value;
        if (! empty($item['lazy']) && empty($item['children']) && ! empty($this->settings['load'])) {
            $provider = app($this->settings['load']);
            $this->loaded[$value] = $provider($value);
            if ($this->settings['checkable'] ?? false) {
                $this->value = $this->cascade((array) $this->value);
            }
        }
    }

    public function choose(string $value): void
    {
        $item = $this->index()[$value] ?? null;
        if (! $item || ! empty($item['disabled']) || ($this->settings['disabled'] ?? false)) {
            return;
        }
        if ($this->settings['checkable'] ?? false) {
            $values = array_fill_keys($this->cascade((array) $this->value), true);
            $check = ! isset($values[$value]);
            $walk = function (array $item) use (&$walk, &$values, $check): void {
                if (! empty($item['disabled'])) {
                    return;
                }
                if ($check) {
                    $values[(string) $item['value']] = true;
                } else {
                    unset($values[(string) $item['value']]);
                }
                foreach ($item['children'] ?? [] as $child) {
                    $walk($child);
                }
            };
            $walk($item);
            $this->value = $this->settle($values);
        } else {
            if (! empty($item['children']) || ! empty($item['lazy'])) {
                $this->expand($value);
            }
            $this->value = $value;
            $this->dispatch('ui-tree-picked');
        }
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return list<string>
     */
    public function cascade(array $values): array
    {
        $selected = array_fill_keys(array_map('strval', $values), true);
        $walk = function (array $items, bool $checked = false, bool $disabled = false) use (&$walk, &$selected): void {
            foreach ($items as $item) {
                $disabledHere = $disabled || ! empty($item['disabled']);
                $chosen = $checked || isset($selected[(string) $item['value']]);
                if ($chosen && ! $disabledHere) {
                    $selected[(string) $item['value']] = true;
                }
                $walk($item['children'] ?? [], $chosen, $disabledHere);
            }
        };
        $walk($this->data());

        return $this->settle($selected);
    }

    /**
     * @param  array<string, bool>  $selected
     * @return list<string>
     */
    private function settle(array $selected): array
    {
        $walk = function (array $item, bool $disabled = false) use (&$walk, &$selected): bool {
            $disabled = $disabled || ! empty($item['disabled']);
            $children = array_filter($item['children'] ?? [], fn ($child) => ! $disabled && empty($child['disabled']));
            if ($children === []) {
                return isset($selected[(string) $item['value']]);
            }
            $all = ! in_array(false, array_map(fn ($child) => $walk($child, $disabled), $children), true);
            if ($all) {
                $selected[(string) $item['value']] = true;
            } else {
                unset($selected[(string) $item['value']]);
            }

            return $all;
        };
        foreach ($this->data() as $item) {
            $walk($item);
        }

        return array_values(array_filter(array_map('strval', array_keys($this->index())), fn ($value) => isset($selected[$value])));
    }

    public function checked(string $value): string
    {
        $selected = ($this->settings['checkable'] ?? false) ? $this->cascade((array) $this->value) : array_map('strval', (array) $this->value);
        if (in_array($value, $selected, true)) {
            return 'true';
        }
        $walk = function (array $items) use (&$walk, $selected): bool {
            foreach ($items as $item) {
                if (in_array((string) $item['value'], $selected, true) || $walk($item['children'] ?? [])) {
                    return true;
                }
            }

            return false;
        };

        return $walk(($this->index()[$value]['children'] ?? [])) ? 'mixed' : 'false';
    }

    /** @param array<string, mixed> $item */
    public function matches(array $item): bool
    {
        if ($this->query === '' || mb_stripos((string) ($item['label'] ?? $item['value']), $this->query) !== false) {
            return true;
        }
        foreach ($item['children'] ?? [] as $child) {
            if ($this->matches($child)) {
                return true;
            }
        }

        return false;
    }

    public function render(): View
    {
        return view('ui::livewire.tree', ['nodes' => $this->data()]);
    }
}
