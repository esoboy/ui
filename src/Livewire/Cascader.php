<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Cascader extends Component
{
    use SyncsModel;

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
    public array $path = [];

    /**
     * @param  array<int|string, mixed>  $options
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $options = [], array $settings = [], mixed $value = []): void
    {
        $this->options = $options;
        $this->settings = $settings;
        $this->value = array_values(array_map('strval', (array) $value));
        $this->path = $this->value;
    }

    public function choose(int $depth, string $value): void
    {
        if (($this->settings['disabled'] ?? false) || $depth < 0) {
            return;
        }
        $options = Options::normalize($this->options);
        $path = [];
        foreach (array_slice($this->path, 0, $depth) as $part) {
            $option = Options::child($options, $part);
            if (! $option || ! empty($option['disabled'])) {
                return;
            }
            $path[] = $part;
            $options = $option['children'] ?? [];
        }
        if (count($path) !== $depth) {
            return;
        }
        $option = Options::child($options, $value);
        if (! $option || ! empty($option['disabled'])) {
            return;
        }
        $this->path = [...$path, $value];
        if (empty($option['children']) || ($this->settings['changeOnSelect'] ?? false)) {
            $this->value = $this->path;
        }
        $this->dispatch('ui-cascader-picked', leaf: empty($option['children']), depth: $depth);
    }

    protected function modelChanged(): void
    {
        $this->path = $this->value;
    }

    public function render(): View
    {
        $options = Options::normalize($this->options);
        $levels = [$options];
        foreach ($this->path as $part) {
            $option = Options::child($options, $part);
            if (! $option || empty($option['children']) || ! empty($option['disabled'])) {
                break;
            }
            $options = $option['children'];
            $levels[] = $options;
        }

        return view('ui::livewire.cascader', ['levels' => $levels, 'labels' => Options::labels(Options::normalize($this->options), $this->value)]);
    }
}
