<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Combobox extends Component
{
    use SyncsModel;

    #[Modelable]
    public mixed $value = null;

    /** @var array<int|string, mixed> */
    #[Reactive]
    public array $options = [];

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public string $query = '';

    /**
     * @param  array<int|string, mixed>  $options
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $options = [], array $settings = [], mixed $value = null): void
    {
        $this->options = $options;
        $this->settings = $settings;
        $this->value = ($settings['multiple'] ?? false) ? array_values(array_map('strval', (array) $value)) : $value;
        $this->query = ($settings['multiple'] ?? false) || ($settings['button'] ?? false) ? '' : $this->label();
    }

    public function label(): string
    {
        if ($this->settings['autocomplete'] ?? false) {
            return (string) $this->value;
        }
        $selected = array_map('strval', (array) $this->value);

        return implode(', ', array_column(array_filter(Options::normalize($this->options), fn ($option) => in_array($option['value'], $selected, true)), 'label'));
    }

    protected function modelChanged(): void
    {
        $this->query = ($this->settings['multiple'] ?? false) || ($this->settings['button'] ?? false) ? '' : $this->label();
    }

    public function updatedQuery(): void
    {
        if (($this->settings['autocomplete'] ?? false) && ! ($this->settings['disabled'] ?? false)) {
            $this->value = $this->query;
        }
    }

    public function choose(string $value): void
    {
        $option = collect(Options::normalize($this->options))->firstWhere('value', $value);
        if (! $option || ! empty($option['disabled']) || ($this->settings['disabled'] ?? false)) {
            return;
        }
        $multiple = $this->settings['multiple'] ?? false;
        $this->value = $multiple ? (in_array($value, (array) $this->value, true) ? array_values(array_diff($this->value, [$value])) : [...(array) $this->value, $value]) : $value;
        $this->query = $multiple || ($this->settings['button'] ?? false) ? '' : $this->label();
        $this->dispatch('ui-combobox-picked', close: ! $multiple);
    }

    public function remove(string $value): void
    {
        if (! ($this->settings['disabled'] ?? false) && ($this->settings['multiple'] ?? false)) {
            $this->value = array_values(array_diff((array) $this->value, [$value]));
        }
    }

    public function clear(): void
    {
        if (! ($this->settings['disabled'] ?? false)) {
            $this->value = ($this->settings['multiple'] ?? false) ? [] : (($this->settings['autocomplete'] ?? false) ? '' : null);
            $this->query = '';
        }
    }

    public function render(): View
    {
        $options = Options::normalize($this->options);
        $query = trim($this->query);
        if (! ($this->settings['autocomplete'] ?? false) && $query === $this->label()) {
            $query = '';
        }
        if (($this->settings['filter'] ?? true) && $query !== '') {
            $options = array_values(array_filter($options, fn ($option) => mb_stripos($option['label'], $query) !== false));
        }

        return view('ui::livewire.combobox', [
            'choices' => $options,
            'label' => $this->label(),
            'labels' => array_column(Options::normalize($this->options), 'label', 'value'),
        ]);
    }
}
