<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Transfer extends Component
{
    /** @var list<string> */
    #[Modelable]
    public array $value = [];

    /** @var array<int|string, mixed> */
    #[Reactive]
    public array $options = [];

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    /** @var list<string> */
    public array $checked = [];

    /** @var array{source: string, target: string} */
    public array $query = ['source' => '', 'target' => ''];

    /**
     * @param  array<int|string, mixed>  $options
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $options = [], array $settings = [], mixed $value = []): void
    {
        $this->options = $options;
        $this->settings = $settings;
        $this->value = array_values(array_map('strval', (array) $value));
    }

    /** @return array<string, array<string, mixed>> */
    public function lists(): array
    {
        $options = Options::normalize($this->options);
        $lists = [];
        foreach (['source', 'target'] as $index => $name) {
            $wanted = array_values(array_filter($options, fn ($option) => in_array($option['value'], $this->value, true) === ($name === 'target')));
            $shown = array_values(array_filter($wanted, fn ($option) => $this->matches($name, $option)));
            $enabled = array_values(array_filter($shown, fn ($option) => empty($option['disabled'])));
            $checked = array_values(array_filter($wanted, fn ($option) => in_array($option['value'], $this->checked, true)));
            $selected = array_filter($enabled, fn ($option) => in_array($option['value'], $this->checked, true));
            $lists[$name] = [
                'title' => $this->settings['titles'][$index] ?? __('ui::transfer.'.$name), 'options' => $wanted,
                'checked' => count($checked), 'empty' => $shown === [], 'all' => $enabled !== [] && count($enabled) === count($selected),
                'mixed' => $selected !== [] && count($selected) < count($enabled), 'enabled' => array_column($enabled, 'value'),
            ];
        }

        return $lists;
    }

    /** @param array<string, mixed> $option */
    public function matches(string $list, array $option): bool
    {
        return ($this->query[$list] ?? '') === '' || mb_stripos($option['label'], $this->query[$list]) !== false;
    }

    public function toggleAll(string $list): void
    {
        if (($this->settings['disabled'] ?? false) || ! isset($this->lists()[$list])) {
            return;
        }
        $data = $this->lists()[$list];
        $this->checked = $data['all'] ? array_values(array_diff($this->checked, $data['enabled'])) : array_values(array_unique([...$this->checked, ...$data['enabled']]));
    }

    public function move(string $target): void
    {
        if (($this->settings['disabled'] ?? false) || ! in_array($target, ['source', 'target'], true)) {
            return;
        }
        $source = $this->lists()[$target === 'target' ? 'source' : 'target'];
        $moving = array_column(array_filter($source['options'], fn ($option) => empty($option['disabled']) && in_array($option['value'], $this->checked, true)), 'value');
        $selected = $target === 'target' ? [...$this->value, ...$moving] : array_diff($this->value, $moving);
        $this->value = array_values(array_filter(array_column(Options::normalize($this->options), 'value'), fn ($value) => in_array($value, $selected, true)));
        $this->checked = array_values(array_diff($this->checked, $moving));
    }

    public function render(): View
    {
        return view('ui::livewire.transfer', [
            'lists' => $this->lists(), 'disabled' => $this->settings['disabled'] ?? false,
            'searchable' => $this->settings['searchable'] ?? false,
        ]);
    }
}
