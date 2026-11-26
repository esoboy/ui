<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;

/** @property-read list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> $visibleRows */
class Tree extends TreeControl
{
    /** @var list<string|int> */
    public array $expanded = [];

    /** @return list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> */
    #[Computed]
    public function visibleRows(): array
    {
        $visible = [];
        $open = [];
        $expanded = array_fill_keys(array_map(self::choiceKey(...), $this->expanded), true);
        foreach ($this->rows as $row) {
            if ($row['parent'] !== null && ! ($open[self::choiceKey($row['parent'])] ?? false)) {
                continue;
            }
            $visible[] = $row;
            $key = self::choiceKey($row['value']);
            $open[$key] = isset($expanded[$key]);
        }

        return $visible;
    }

    public function expand(string|int $value): void
    {
        if ($this->disabled) {
            return;
        }
        foreach ($this->rows as $row) {
            if ($row['value'] === $value && $row['branch']) {
                $this->expanded = in_array($value, $this->expanded, true) ? array_values(array_filter($this->expanded, fn ($item): bool => $item !== $value)) : [...$this->expanded, $value];
                unset($this->visibleRows);

                return;
            }
        }
    }

    public function render(): View
    {
        return view('ui::livewire.tree');
    }
}
