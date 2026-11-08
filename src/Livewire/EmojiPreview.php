<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EmojiPreview extends Component
{
    /** @var array<string, mixed> */
    #[Locked]
    public array $footerAttributes = [];

    #[Locked]
    public string $glyph = '';

    #[Locked]
    public string $label = '';

    public function preview(string $glyph, string $label): void
    {
        $this->glyph = mb_substr($glyph, 0, 30);
        $this->label = mb_substr($label, 0, 150);
    }

    public function render(): View
    {
        return view('ui::livewire.emoji-preview');
    }
}
