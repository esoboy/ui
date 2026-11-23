<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/** @property-read list<array{value: string|int|null, label: string, disabled: bool}> $results */
class Combobox extends PopupOptionControl
{
    use SearchesChoices;

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

    public function updatingQuery(): void
    {
        $this->searching = true;
    }

    public function finishSearch(): void
    {
        $this->searching = false;
        $this->syncQueryToSelection();
    }

    private function syncQueryToSelection(): void
    {
        $this->query = $this->multiple || $this->searchInPopup ? '' : $this->label;
        unset($this->results);
    }

    protected function searchTerm(): string
    {
        return $this->searching ? $this->query : '';
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
        if ($previousKeys !== array_keys($remembered)) {
            $this->finishSearch();
        } elseif (! $this->searching) {
            $this->syncQueryToSelection();
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
        unset($this->selected, $this->selectedKeys, $this->selectedChoices, $this->label);
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
