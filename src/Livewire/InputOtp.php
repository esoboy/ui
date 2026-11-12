<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\HtmlFragment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class InputOtp extends Component
{
    #[Modelable]
    public mixed $value = '';

    #[Reactive]
    public string $layout = '';

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public function updatedValue(): void
    {
        $this->value = $this->code();
    }

    public function render(): View
    {
        $code = $this->code();
        $document = HtmlFragment::parse($this->layout);
        foreach ($document->querySelectorAll('[data-slot="input-otp-slot"][data-index]') as $cell) {
            $index = (int) $cell->getAttribute('data-index');
            $cell->textContent = $index < 0 ? '' : mb_substr($code, $index, 1);
        }
        $cells = '';
        foreach ($document->body->childNodes ?? [] as $part) {
            $cells .= $document->saveHtml($part);
        }

        return view('ui::livewire.input-otp', ['code' => $code, 'cells' => $cells]);
    }

    private function code(): string
    {
        return mb_substr(is_scalar($this->value) ? (string) $this->value : '', 0, max(1, (int) ($this->settings['maxlength'] ?? 6)));
    }
}
