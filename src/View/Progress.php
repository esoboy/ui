<?php

namespace Esoboy\Ui\View;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Progress extends Component
{
    public readonly self $progress;

    public function __construct(public readonly mixed $value = null, public readonly mixed $max = 100,
        public readonly ?string $status = null, public readonly string $size = 'default')
    {
        $this->progress = $this;
    }

    public function render(): View
    {
        return view('ui::components.progress.index');
    }
}
