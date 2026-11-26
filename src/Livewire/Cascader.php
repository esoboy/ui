<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/**
 * @property-read list<list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}>> $columns
 * @property-read array<string|int, array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> $pathRows
 */
class Cascader extends TreeControl
{
    #[Reactive]
    public string $placeholder = '';

    /** @var list<string|int> */
    #[Locked]
    public array $path = [];

    /** @var list<string|int> Selection last used to reconcile browsing, not another authority. */
    #[Locked]
    public array $navigationSelection = [];

    /** @var list<string|int>|null A controlled choice can be echoed on a later parent render. */
    #[Locked]
    public ?array $requestedSelection = null;

    public function mount(mixed $initialValue = null): void
    {
        parent::mount($initialValue ?? ($this->multiple ? [] : null));
    }

    /** @return array<string|int, array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> */
    #[Computed]
    public function pathRows(): array
    {
        $rows = [];
        foreach ($this->rows as $row) {
            $rows[self::choiceKey($row['value'])] = $row;
        }

        return $rows;
    }

    /** @return list<list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}>> */
    #[Computed]
    public function columns(): array
    {
        $groups = [];
        foreach ($this->rows as $row) {
            $key = self::choiceKey($row['parent']);
            $groups[$key][] = $row;
        }
        $root = self::choiceKey(null);
        $columns = isset($groups[$root]) ? [$groups[$root]] : [];
        foreach ($this->path as $parent) {
            if (isset($groups[self::choiceKey($parent)])) {
                $columns[] = $groups[self::choiceKey($parent)];
            }
        }

        return $columns;
    }

    public function choose(string|int|null $value): void
    {
        $path = $this->pathTo($value);
        if ($path === [] || $this->disabled || $this->readonly) {
            return;
        }
        if (! $this->pathRows[self::choiceKey($value)]['branch']) {
            $this->path = $path;
        }
        parent::choose($value);
    }

    protected function changeValue(mixed $value): void
    {
        parent::changeValue($value);
        $selection = $this->selectionValues($value);
        if ($this->mode === 'controlled') {
            $this->requestedSelection = $selection;
        } else {
            $this->navigationSelection = $selection;
        }
    }

    public function expand(string|int $value): void
    {
        $path = $this->pathTo($value);
        if (! $this->disabled && ! $this->readonly && $path !== [] && $this->pathRows[self::choiceKey($value)]['branch']) {
            $this->path = $path;
            $this->requestedSelection = null;
            unset($this->columns);
        }
    }

    public function render(): View
    {
        $this->reconcilePath();

        return view('ui::livewire.cascader');
    }

    /** @return list<string|int> */
    private function pathTo(string|int|null $value): array
    {
        $byValue = $this->pathRows;
        $path = [];
        while ($value !== null) {
            $row = $byValue[self::choiceKey($value)] ?? null;
            if ($row === null || $row['value'] !== $value || $row['disabled']) {
                return [];
            }
            array_unshift($path, $row['value']);
            $value = $row['parent'];
        }

        return $path;
    }

    private function reconcilePath(): void
    {
        $selection = $this->selected;
        $selectedPath = $this->pathTo($selection === [] ? null : $selection[array_key_last($selection)]);
        if ($selection !== $this->navigationSelection) {
            if ($selection !== $this->requestedSelection) {
                $this->path = $selectedPath;
            }
            $this->navigationSelection = $selection;
            $this->requestedSelection = null;
        } elseif ($this->path === [] && $selectedPath !== []) {
            // Late-loaded nodes must be visible without opening a locally checked branch.
            $last = $selectedPath[array_key_last($selectedPath)];
            $this->path = $this->pathRows[self::choiceKey($last)]['branch'] ? array_slice($selectedPath, 0, -1) : $selectedPath;
        }
        $valid = [];
        foreach ($this->path as $value) {
            if ($this->pathTo($value) !== [...$valid, $value]) {
                $this->path = $selectedPath !== [] ? $selectedPath : $valid;
                break;
            }
            $valid[] = $value;
        }
        unset($this->columns);
    }
}
