<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

/** @property-read list<string> $tags */
class TagsInput extends ValueControl
{
    use HasControlSize;

    public string $draft = '';

    #[Reactive]
    public ?int $max = null;

    #[Reactive]
    public int $maxLength = 100;

    /** @return list<string> */
    #[Computed]
    public function tags(): array
    {
        $value = $this->current ?? [];
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidArgumentException(__('ui::errors.tag_list'));
        }
        foreach ($value as $tag) {
            if (! is_string($tag)) {
                throw new InvalidArgumentException(__('ui::errors.tag_list'));
            }
        }

        return $value;
    }

    public function add(): void
    {
        $tag = trim($this->draft);
        if ($this->disabled || $this->readonly || $tag === '' || mb_strlen($tag) > $this->maxLength || in_array($tag, $this->tags, true) || ($this->max !== null && count($this->tags) >= $this->max)) {
            return;
        }
        $this->changeValue([...$this->tags, $tag]);
        $this->draft = '';
        unset($this->tags);
    }

    public function remove(string $tag, ?int $index = null): void
    {
        $tags = $this->tags;
        $index ??= array_search($tag, $tags, true);
        if ($index === false || ($tags[$index] ?? null) !== $tag) {
            return;
        }
        array_splice($tags, $index, 1);
        $this->changeValue($tags);
        unset($this->tags);
    }

    public function resetFormValue(): void
    {
        $this->draft = '';
        parent::resetFormValue();
    }

    public function render(): View
    {
        return view('ui::livewire.tags-input');
    }
}
