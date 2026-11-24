<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Pagination extends Component
{
    // Scoped slots can inherit this group's size without picking up an unrelated ancestor's size.
    public readonly string $paginationSize;

    /** @param Paginator<array-key, mixed>|CursorPaginator<array-key, mixed>|null $paginator */
    public function __construct(
        public readonly Paginator|CursorPaginator|null $paginator = null,
        public readonly ?int $maxPages = null,
        public readonly bool $jumpable = false,
        string $size = 'default',
    ) {
        $this->paginationSize = $size;
    }

    public function render(): View
    {
        return view('ui::components.pagination.index');
    }
}
