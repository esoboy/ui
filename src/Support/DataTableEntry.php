<?php

namespace Esoboy\Ui\Support;

final readonly class DataTableEntry
{
    /** @param array<array-key, mixed>|object $row */
    public function __construct(
        public array|object $row,
        public string $id,
        public ?string $parent = null,
        public int $depth = 0,
        public bool $hasChildren = false,
    ) {}
}
