<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\HtmlFragment;
use Esoboy\Ui\Support\SearchText;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Command extends Component
{
    #[Reactive]
    public string $markup = '';

    #[Locked]
    public bool $hasParent = false;

    #[Reactive]
    public ?string $boundQuery = null;

    public string $query = '';

    public function mount(): void
    {
        $this->updatedBoundQuery();
    }

    public function updatedBoundQuery(): void
    {
        $this->query = $this->boundQuery ?? '';
    }

    public function search(string $query): void
    {
        $this->query = $query;
    }

    public function render(): View
    {
        $document = HtmlFragment::parse($this->markup);
        $query = trim($this->query);
        $matches = 0;
        foreach ($document->querySelectorAll('[data-slot="command-item"]') as $item) {
            $text = $item->getAttribute('data-value').' '.$item->textContent.' '.$item->getAttribute('data-keywords');
            if ($item->hasAttribute('hidden') || ! SearchText::matches($query, $text)) {
                $item->setAttribute('hidden', '');
            } else {
                $matches++;
            }

        }
        if ($this->hasParent) {
            foreach ($document->querySelectorAll('[data-slot="command-item"], [data-slot="command-item"] *') as $control) {
                if ($control->closest('[wire\\:id]')) {
                    continue;
                }
                foreach ($control->attributes as $attribute) {
                    if (explode('.', $attribute->name)[0] === 'wire:click'
                        && preg_match('/^(?:[a-zA-Z_]|\$(?:set|toggle|refresh|commit|call)\b)/', $attribute->value)) {
                        $attribute->value = '$parent.'.$attribute->value;
                    }
                }
            }
        }
        foreach ($document->querySelectorAll('[data-slot="command-group"]') as $group) {
            if (! $group->querySelector('[data-slot="command-item"]:not([hidden])')) {
                $group->setAttribute('hidden', '');
            }
        }
        if ($query !== '') {
            foreach ($document->querySelectorAll('[data-slot="command-separator"]') as $separator) {
                $separator->setAttribute('hidden', '');
            }
        }
        if ($input = $document->querySelector('[data-command-input]')) {
            if ($this->boundQuery === null) {
                $input->setAttribute('wire:model.live.debounce.100ms', 'query');
            } else {
                foreach ($input->attributes as $attribute) {
                    if (explode('.', $attribute->name)[0] === 'wire:model') {
                        $attribute->value = '$parent.'.$attribute->value;
                    }
                }
                $input->setAttribute('wire:input.debounce.100ms', 'search($event.target.value)');
            }
            $input->setAttribute('value', $this->query);
        }

        $content = '';
        foreach ($document->body->childNodes ?? [] as $child) {
            $content .= $document->saveHtml($child);
        }

        return view('ui::livewire.command', ['content' => $content, 'empty' => $matches === 0]);
    }
}
