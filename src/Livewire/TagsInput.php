<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TagsInput extends Component
{
    /** @var list<string> */
    #[Modelable]
    public array $value = [];

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public string $entry = '';

    /**
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $settings = [], mixed $value = []): void
    {
        $this->settings = $settings;
        $this->value = array_values(array_map('strval', (array) $value));
    }

    public function add(): void
    {
        if ($this->settings['disabled'] ?? false) {
            return;
        }
        foreach ((preg_split('/[,，\r\n]+/u', $this->entry) ?: []) as $text) {
            $tag = trim($text);
            if ($tag !== '' && ! in_array($tag, $this->value, true) && (! ($this->settings['max'] ?? null) || count($this->value) < $this->settings['max'])) {
                $this->value[] = $tag;
            }
        }
        $this->entry = '';
    }

    public function remove(string $tag): void
    {
        if (! ($this->settings['disabled'] ?? false)) {
            $this->value = array_values(array_diff($this->value, [$tag]));
        }
    }

    public function removeLast(): void
    {
        if ($this->entry === '' && ! ($this->settings['disabled'] ?? false)) {
            array_pop($this->value);
        }
    }

    public function render(): View
    {
        return view('ui::livewire.tags-input');
    }
}
