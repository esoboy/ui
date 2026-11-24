<?php

namespace Esoboy\Ui\Livewire;

use InvalidArgumentException;
use Livewire\Attributes\Computed;

/**
 * @property-read list<array{value: string|int|null, label: string, disabled: bool}> $choices
 * @property-read list<string|int> $selected
 * @property-read string $label
 * @property-read list<array{value: string|int|null, label: string, disabled: bool}> $selectedChoices
 * @property-read array<string, array{value: string|int|null, label: string, disabled: bool}> $indexedChoices
 * @property-read array<string, true> $selectedKeys
 */
abstract class ChoiceControl extends ValueControl
{
    use HasControlSize;
    use HasEmptyState;

    abstract protected function multipleSelection(): bool;

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    abstract public function choices(): array;

    /** @return list<string|int> */
    #[Computed]
    public function selected(): array
    {
        return $this->selectionValues($this->current);
    }

    /** @return list<string|int> */
    protected function selectionValues(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        $selection = $this->multipleSelection() ? $value : [$value];
        if (! is_array($selection) || ! array_is_list($selection)) {
            throw new InvalidArgumentException(__('ui::errors.multiple_values'));
        }
        foreach ($selection as $item) {
            if (! is_string($item) && ! is_int($item)) {
                throw new InvalidArgumentException(__('ui::errors.selection_values'));
            }
        }

        return $selection;
    }

    #[Computed]
    public function label(): string
    {
        return implode(__('ui::ui.list_separator'), array_column($this->selectedChoices, 'label'));
    }

    protected static function choiceKey(string|int|null $value): string
    {
        return get_debug_type($value).':'.$value;
    }

    /** @return array<string, array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function indexedChoices(): array
    {
        $options = [];
        foreach ($this->choices as $option) {
            $options[self::choiceKey($option['value'])] = $option;
        }

        return $options;
    }

    /** @return array{value: string|int|null, label: string, disabled: bool}|null */
    protected function optionFor(string|int|null $value): ?array
    {
        return $this->indexedChoices[self::choiceKey($value)] ?? null;
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function selectedChoices(): array
    {
        $selected = [];
        foreach ($this->selected as $value) {
            $option = $this->optionFor($value);
            if ($option !== null) {
                $selected[] = $option;
            }
        }

        return $selected;
    }

    /** @return array<string, true> */
    #[Computed]
    public function selectedKeys(): array
    {
        $keys = [];
        foreach ($this->selected as $value) {
            $keys[self::choiceKey($value)] = true;
        }

        return $keys;
    }

    public function isSelected(string|int|null $value): bool
    {
        if ($value === null) {
            return ! $this->multipleSelection() && $this->current === null;
        }

        return isset($this->selectedKeys[self::choiceKey($value)]);
    }

    public function choose(string|int|null $value): void
    {
        if ($value === null && $this->multipleSelection()) {
            return;
        }
        $option = $this->optionFor($value);
        if ($option === null || $option['disabled']) {
            return;
        }

        $selection = $this->selected;
        $this->changeValue($this->multipleSelection()
            ? (in_array($value, $selection, true) ? array_values(array_filter($selection, fn ($item) => $item !== $value)) : [...$selection, $value])
            : $value);
        unset($this->selected, $this->selectedKeys, $this->selectedChoices, $this->label);
    }
}
