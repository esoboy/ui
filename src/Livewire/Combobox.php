<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/** @property-read list<array{value: string|int|null, label: string, disabled: bool}> $results */
class Combobox extends SearchableSelect
{
    #[Reactive]
    public bool $clearable = false;

    #[Reactive]
    public bool $showRemove = true;

    #[Reactive]
    public bool $showTrigger = true;

    #[Reactive]
    public ?string $prefixIcon = null;

    #[Reactive]
    public bool $searchInPopup = false;

    #[Reactive]
    public string $searchPlaceholder = '';

    #[Reactive]
    public bool $autoHighlight = false;

    #[Locked]
    public bool $searching = false;

    /** @var array<string, array{value: string|int|null, label: string, disabled: bool}|null> Last known options for the selected IDs only. */
    #[Locked]
    public array $rememberedChoices = [];

    public function updatedQuery(): void
    {
        $this->searching = true;
        parent::updatedQuery();
    }

    public function finishSearch(): void
    {
        $this->searching = false;
        $this->query = $this->multiple || $this->searchInPopup ? '' : $this->label;
        unset($this->results);
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function results(): array
    {
        $query = $this->searching ? Str::lower(trim($this->query)) : '';

        return array_values(array_filter($this->choices, fn (array $option): bool => $query === '' || Str::contains(Str::lower($option['label']), $query)));
    }

    public function rendering(): void
    {
        parent::rendering();
        $previousKeys = array_keys($this->rememberedChoices);
        $remembered = array_fill_keys(array_keys($this->selectedKeys), null);
        foreach ($this->selectedChoices as $option) {
            $remembered[self::choiceKey($option['value'])] = [
                'value' => $option['value'], 'label' => $option['label'], 'disabled' => $option['disabled'],
            ];
        }
        $this->rememberedChoices = $remembered;
        if (! $this->searching || $previousKeys !== array_keys($remembered)) {
            $this->finishSearch();
        }
    }

    protected function optionFor(string|int|null $value): ?array
    {
        $option = parent::optionFor($value);
        if ($option !== null || ! $this->isSelected($value)) {
            return $option;
        }

        return $this->rememberedChoices[self::choiceKey($value)] ?? null;
    }

    public function choose(string|int|null $value): void
    {
        parent::choose($value);
        $this->finishSearch();
    }

    public function clearSelection(): void
    {
        $this->changeValue($this->multiple ? [] : null);
        $this->query = '';
        unset($this->selected, $this->selectedKeys, $this->selectedChoices, $this->label, $this->results);
        $this->finishSearch();
    }

    public function removeLast(): void
    {
        if ($this->multiple && $this->selectedChoices !== []) {
            $this->choose($this->selectedChoices[array_key_last($this->selectedChoices)]['value']);
        }
    }

    public function render(): View
    {
        return view('ui::livewire.combobox');
    }
}
