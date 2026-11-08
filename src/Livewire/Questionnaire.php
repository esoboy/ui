<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Questionnaire extends Component
{
    /** @var array<string, mixed> */
    #[Modelable]
    public array $value = [];

    /** @var list<array<string, mixed>> */
    #[Reactive]
    public array $items = [];

    #[Reactive]
    public ?string $shortcuts = null;

    #[Reactive]
    public ?string $questionView = null;

    public ?string $current = null;

    /** @var list<string> */
    public array $attempted = [];

    /** @var list<string> */
    public array $skipped = [];

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $value
     */
    public function mount(array $items = [], array $value = [], ?string $current = null, ?string $shortcuts = null, ?string $questionView = null): void
    {
        $this->items = $items;
        $this->value = $value;
        $this->shortcuts = $shortcuts;
        $this->questionView = $questionView;
        $this->current = collect($this->steps())->firstWhere('name', $current)['name'] ?? ($this->steps()[0]['name'] ?? null);
        foreach ($items as $item) {
            $this->value[$item['name']] ??= ($item['multiple'] ?? false) ? [] : null;
            if ($item['other'] ?? false) {
                $this->value[$item['other']] ??= '';
            }
        }
    }

    /** @return list<array<string, mixed>> */
    public function steps(): array
    {
        return array_values(array_filter($this->items, fn ($item) => empty($item['disabled'])));
    }

    /** @param array<string, mixed> $item */
    public function answered(array $item): bool
    {
        $answer = $this->value[$item['name']] ?? null;
        $options = Options::normalize($item['options'] ?? []);
        $enabled = array_column(array_filter($options, fn ($option) => empty($option['disabled'])), 'value');
        $chosen = array_intersect(array_map('strval', (array) $answer), $enabled);
        $text = $this->value[$item['other'] ?? $item['name']] ?? '';

        return $chosen !== [] || (($item['input'] ?? false) && is_string($text) && trim($text) !== '');
    }

    /** @param array<string, mixed> $item */
    public function settled(array $item): bool
    {
        return $this->answered($item) || (empty($item['required']) && in_array($item['name'], $this->skipped, true));
    }

    public function choose(string $name, string $value): void
    {
        $item = collect($this->steps())->firstWhere('name', $name);
        $option = $item ? collect(Options::normalize($item['options'] ?? []))->firstWhere('value', $value) : null;
        if (! $option || ! empty($option['disabled'])) {
            return;
        }

        $chosen = (array) ($this->value[$name] ?? []);
        $this->value[$name] = ($item['multiple'] ?? false)
            ? (in_array($value, $chosen, true) ? array_values(array_diff($chosen, [$value])) : [...$chosen, $value])
            : $value;
        $this->updatedValue($this->value[$name], $name);
    }

    public function updatedValue(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }
        foreach ($this->items as $item) {
            if ($key !== $item['name'] && $key !== ($item['other'] ?? null)) {
                continue;
            }
            $this->skipped = array_values(array_diff($this->skipped, [$item['name']]));
            if (empty($item['multiple']) && ! empty($value) && isset($item['other'])) {
                $this->value[$key === $item['name'] ? $item['other'] : $item['name']] = $key === $item['name'] ? '' : null;
            }
        }
    }

    public function previous(): void
    {
        $this->move(-1);
    }

    public function next(): void
    {
        $item = collect($this->steps())->firstWhere('name', $this->current);
        if (! $item) {
            return;
        }
        $this->attempted[] = $item['name'];
        if ($this->settled($item)) {
            $this->move(1);
        } else {
            $this->dispatch('ui-questionnaire-step');
        }
    }

    public function skip(): void
    {
        $item = collect($this->steps())->firstWhere('name', $this->current);
        if (! $item || ! empty($item['required'])) {
            return;
        }
        $this->value[$item['name']] = ($item['multiple'] ?? false) ? [] : null;
        if (isset($item['other'])) {
            $this->value[$item['other']] = '';
        }
        $this->skipped[] = $item['name'];
        $this->move(1);
    }

    protected function move(int $offset): void
    {
        $steps = $this->steps();
        $index = array_search($this->current, array_column($steps, 'name'), true);
        if ($offset > 0 && $index === count($steps) - 1) {
            $this->submit();

            return;
        }
        $this->current = $steps[$index + $offset]['name'] ?? $this->current;
        $this->dispatch('ui-questionnaire-step');
    }

    public function submit(): void
    {
        foreach ($this->steps() as $item) {
            $this->attempted[] = $item['name'];
            if (! $this->settled($item)) {
                $this->current = $item['name'];
                $this->dispatch('ui-questionnaire-step');

                return;
            }
        }
        foreach ($this->items as $item) {
            $this->value[$item['name']] ??= ($item['multiple'] ?? false) ? [] : null;
            if (isset($item['other'])) {
                $this->value[$item['other']] ??= '';
            }
        }
        $this->dispatch('finish', value: $this->value);
    }

    public function render(): View
    {
        $steps = $this->steps();

        return view('ui::livewire.questionnaire', ['steps' => $steps, 'index' => array_search($this->current, array_column($steps, 'name'), true)]);
    }
}
