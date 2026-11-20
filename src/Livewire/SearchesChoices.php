<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

/** @property-read list<array{value: string|int|null, label: string, disabled: bool}> $results */
trait SearchesChoices
{
    public string $query = '';

    public function updatedQuery(): void
    {
        $this->dispatch('query-changed', query: $this->query);
    }

    protected function searchTerm(): string
    {
        return $this->query;
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function results(): array
    {
        $query = Str::lower(trim($this->searchTerm()));

        if ($query === '') {
            return $this->choices;
        }

        return array_values(array_filter($this->choices, fn (array $option): bool => Str::contains(Str::lower($option['label']), $query)));
    }
}
