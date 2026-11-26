<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Options;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

/**
 * @phpstan-type Option array{value: string|int, label: string, disabled: bool}
 * @phpstan-type Panel array{items: list<Option>, emptyMessage: ?string}
 *
 * @property-read list<Option> $results
 * @property-read array<string, Panel> $panels
 */
class Transfer extends ChoiceControl
{
    use SearchesChoices;

    /** @var list<array{value: string|int, label: string, disabled?: bool}> */
    #[Reactive]
    public array $options = [];

    protected function multipleSelection(): bool
    {
        return true;
    }

    /** @return list<array{value: string|int, label: string, disabled: bool}> */
    #[Computed]
    public function choices(): array
    {
        return Options::validate($this->options);
    }

    /** @return array<string, Panel> */
    #[Computed]
    public function panels(): array
    {
        $panels = [];
        foreach (['available' => false, 'selected' => true] as $side => $selected) {
            $onSide = fn (array $option): bool => $this->isSelected($option['value']) === $selected;
            $items = array_values(array_filter($this->results, $onSide));
            $emptyMessage = match (true) {
                $items !== [] => null,
                array_any($this->choices, $onSide) => 'matches',
                $selected => 'selection',
                $this->choices === [] => 'options',
                default => 'all_selected',
            };
            $panels[$side] = ['items' => $items, 'emptyMessage' => $emptyMessage];
        }

        return $panels;
    }

    /** @return list<string|int> */
    public function movedSelection(bool $select): array
    {
        $selected = [];
        foreach ($this->selected as $value) {
            $selected[get_debug_type($value).':'.$value] = $value;
        }
        foreach ($this->results as $option) {
            if ($option['disabled']) {
                continue;
            }
            $key = get_debug_type($option['value']).':'.$option['value'];
            if ($select) {
                $selected[$key] = $option['value'];
            } else {
                unset($selected[$key]);
            }
        }

        return array_values($selected);
    }

    public function moveAll(bool $select): void
    {
        $this->changeValue($this->movedSelection($select));
        unset($this->selected, $this->selectedKeys, $this->selectedChoices, $this->label);
    }

    public function render(): View
    {
        return view('ui::livewire.transfer');
    }
}
