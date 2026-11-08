<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ImagePreview extends Component
{
    /** @var list<array{src: string, alt: string}> */
    #[Reactive]
    public array $images = [];

    #[Locked]
    public ?int $index = null;

    #[Locked]
    public ?string $controlsView = null;

    public function select(int $index): void
    {
        if (isset($this->images[$index])) {
            $this->index = $index;
        }
    }

    public function previous(): void
    {
        if (($index = $this->currentIndex()) !== null) {
            $this->select($index - 1);
        }
    }

    public function next(): void
    {
        if (($index = $this->currentIndex()) !== null) {
            $this->select($index + 1);
        }
    }

    private function currentIndex(): ?int
    {
        return $this->index === null || $this->images === [] ? null : min($this->index, count($this->images) - 1);
    }

    public function render(): View
    {
        $selected = $this->currentIndex();
        $image = $selected === null ? null : $this->images[$selected];
        $total = count($this->images);

        return view('ui::livewire.image-preview', compact('selected', 'image', 'total'));
    }
}
