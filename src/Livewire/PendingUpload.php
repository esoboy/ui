<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\UploadedFiles;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PendingUpload extends Component
{
    #[Locked]
    public ?string $list = null;

    #[Locked]
    public string $orientation = 'horizontal';

    #[Locked]
    public int $operation = 0;

    #[Locked]
    public string $phase = 'idle';

    #[Locked]
    public int $progress = 0;

    /** @var list<array{name: string, size: string, preview: ?string}> */
    #[Locked]
    public array $files = [];

    /** @param list<array<string, mixed>> $files */
    public function begin(int $operation, array $files): void
    {
        if ($operation <= $this->operation) {
            return;
        }

        $this->operation = $operation;
        $this->phase = 'uploading';
        $this->progress = 0;
        $this->files = [];
        foreach ($files as $file) {
            $preview = $file['preview'] ?? null;
            $this->files[] = [
                'name' => (string) ($file['name'] ?? ''),
                'size' => UploadedFiles::size(max(0, (int) ($file['size'] ?? 0))),
                'preview' => is_string($preview) && str_starts_with($preview, 'blob:') ? $preview : null,
            ];
        }
    }

    public function advance(int $operation, int $progress): void
    {
        if ($this->operation === $operation && $this->phase === 'uploading') {
            $this->progress = max($this->progress, min(100, $progress));
        }
    }

    public function finish(int $operation, string $phase): void
    {
        if ($this->operation !== $operation || ! in_array($phase, ['finish', 'error', 'cancel'], true)) {
            return;
        }

        $this->phase = $phase;
        $this->files = [];
    }

    public function render(): View
    {
        return view('ui::livewire.pending-upload');
    }
}
