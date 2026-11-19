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

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function results(): array
    {
        $query = Str::lower(trim($this->query));

        return array_values(array_filter($this->choices, fn (array $option): bool => $query === '' || Str::contains(Str::lower($option['label']), $query)));
    }
}
