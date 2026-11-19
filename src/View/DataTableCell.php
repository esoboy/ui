<?php

namespace Esoboy\Ui\View;

use Closure;
use Illuminate\View\Component;

final class DataTableCell extends Component
{
    private readonly DataTable $table;

    private readonly string $key;

    public function __construct(private readonly string $column, mixed $row)
    {
        $this->table = DataTable::enclosing();
        $this->key = $this->table->keyFor($row);
    }

    public function shouldRender(): bool
    {
        return isset($this->table->records[$this->key], $this->table->layout->visible[$this->column]);
    }

    public function render(): Closure
    {
        return function (array $data): string {
            $this->table->rememberCell($this->key, $this->column, $data['slot']);

            return '';
        };
    }
}
