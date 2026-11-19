<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

/**
 * @property-read list<array{value: string|int, label: string, disabled: bool}> $suggestions
 * @property-read ?string $queryToken
 */
class Mentions extends ValueControl
{
    use HasEmptyState;

    /** @var array<array-key, mixed> */
    #[Reactive]
    public array $options = [];

    #[Computed]
    public function queryToken(): ?string
    {
        return is_string($this->current) && preg_match('/(?:^|\s)@([^\s@]*)$/u', $this->current, $match) ? $match[1] : null;
    }

    /** @return list<array{value: string|int, label: string, disabled: bool}> */
    #[Computed]
    public function suggestions(): array
    {
        if ($this->queryToken === null) {
            return [];
        }
        $query = Str::lower($this->queryToken);

        return array_values(array_filter(Options::validate($this->options), fn (array $option): bool => Str::contains(Str::lower($option['label']), $query) || Str::contains(Str::lower((string) $option['value']), $query)));
    }

    public function choose(string|int $value, string $query): void
    {
        if ($query !== $this->queryToken) {
            return;
        }
        foreach ($this->suggestions as $option) {
            if ($option['value'] === $value && ! $option['disabled'] && is_string($this->current)) {
                $text = preg_replace_callback('/@[^\s@]*$/u', fn (): string => '@'.$value.' ', $this->current);
                $this->changeValue($text);
                unset($this->queryToken, $this->suggestions);

                return;
            }
        }
    }

    public function render(): View
    {
        return view('ui::livewire.mentions');
    }
}
