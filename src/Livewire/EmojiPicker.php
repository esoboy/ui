<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Emoji;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class EmojiPicker extends Component
{
    #[Modelable]
    public ?string $value = null;

    #[Reactive]
    public int $columns = 9;

    #[Reactive]
    public bool $footer = false;

    public string $query = '';

    public function pick(string $emoji): void
    {
        foreach (Emoji::catalog() as $emojis) {
            foreach ($emojis as [$text, $label]) {
                if ($text !== $emoji) {
                    continue;
                }
                $this->value = $text;
                $this->dispatch('emoji-select', emoji: $text, label: $label);

                return;
            }
        }
    }

    public function render(): View
    {
        $query = mb_strtolower(trim($this->query));
        $categories = [];
        foreach (Emoji::catalog() as $category => $emojis) {
            if ($query !== '') {
                $score = fn ($emoji) => Emoji::score($emoji[1], $emoji[2], $query);
                $emojis = array_values(array_filter($emojis, fn ($emoji) => $score($emoji) > 0));
                usort($emojis, fn ($a, $b) => $score($b) <=> $score($a));
            }
            if ($emojis !== []) {
                $categories[$category] = $emojis;
            }
        }

        return view('ui::livewire.emoji-picker', ['categories' => $categories]);
    }
}
