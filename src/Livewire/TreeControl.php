<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\TreeNodes;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

/** @property-read list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> $rows */
abstract class TreeControl extends ChoiceControl
{
    /** @var array<array-key, mixed> */
    #[Reactive]
    public array $nodes = [];

    #[Reactive]
    public bool $multiple = false;

    #[Reactive]
    public bool $selectParents = false;

    protected function multipleSelection(): bool
    {
        return $this->multiple;
    }

    /** @return list<array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}> */
    #[Computed]
    public function rows(): array
    {
        return TreeNodes::flatten($this->nodes);
    }

    /** @return list<array{value: string|int, label: string, disabled: bool}> */
    #[Computed]
    public function choices(): array
    {
        return array_map(fn (array $row): array => [
            'value' => $row['value'],
            'label' => $row['label'],
            'disabled' => $row['disabled'] || ($row['branch'] && ! $this->selectParents),
        ], $this->rows);
    }
}
