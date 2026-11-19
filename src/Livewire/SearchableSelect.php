<?php

namespace Esoboy\Ui\Livewire;

/** @property-read list<array{value: string|int|null, label: string, disabled: bool}> $results */
abstract class SearchableSelect extends Select
{
    use SearchesChoices;
}
