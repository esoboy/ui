<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Icons;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * @property-read int $resultPage
 * @property-read int $lastResultPage
 */
class IconSelect extends Combobox
{
    private const int PAGE_SIZE = 32;

    #[Locked]
    public ?int $browsePage = null;

    #[Computed]
    public function resultPage(): int
    {
        if ($this->browsePage !== null) {
            return $this->browsePage;
        }

        $index = array_search($this->current, Icons::names(), true);

        return $index === false ? 1 : intdiv($index, self::PAGE_SIZE) + 1;
    }

    public function updatingQuery(): void
    {
        parent::updatingQuery();
        $this->browsePage = 1;
    }

    public function showResultPage(int $page): void
    {
        $this->browsePage = max(1, min($page, $this->lastResultPage));
    }

    #[Computed]
    public function lastResultPage(): int
    {
        return max(1, (int) ceil(count($this->results) / self::PAGE_SIZE));
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function pageResults(): array
    {
        return array_slice($this->results, ($this->resultPage - 1) * self::PAGE_SIZE, self::PAGE_SIZE);
    }

    public function confirmIcon(string $name): void
    {
        if ($this->value === $name) {
            $this->choose($name);
        } else {
            $this->finishSearch();
        }
    }

    public function finishSearch(): void
    {
        parent::finishSearch();
        $this->browsePage = null;
        unset($this->resultPage, $this->lastResultPage, $this->pageResults);
    }

    /** @return list<array{value: string, label: string, icon: string, disabled: bool}> */
    #[Computed]
    public function choices(): array
    {
        return array_map(static fn (string $name): array => [
            'value' => $name,
            'label' => $name,
            'icon' => $name,
            'disabled' => false,
        ], Icons::names());
    }

    public function render(): View
    {
        return parent::render()->with('paginated', true);
    }
}
