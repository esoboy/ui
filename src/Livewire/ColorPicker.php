<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ColorPicker extends Component
{
    #[Modelable]
    public mixed $value = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public function setColor(string $value): void
    {
        if ($this->settings['disabled'] ?? false) {
            return;
        }

        $color = $this->normalize($value);
        if ($color !== null || trim($value) === '') {
            $this->value = $color;
        }
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $hex = strtolower(trim($value));
        $hex = str_starts_with($hex, '#') ? substr($hex, 1) : $hex;
        if (strlen($hex) === 3) {
            $hex = implode('', array_map(fn ($digit) => $digit.$digit, str_split($hex)));
        }

        return preg_match('/\A[0-9a-f]{6}\z/', $hex) === 1 ? '#'.$hex : null;
    }

    public function render(): View
    {
        $color = $this->normalize($this->value);
        $swatches = array_filter(array_map($this->normalize(...), $this->settings['swatches'] ?? []));

        return view('ui::livewire.color-picker', compact('color', 'swatches'));
    }
}
