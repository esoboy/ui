<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * File Upload and Image Upload bound to Livewire uploads, inside fields.
 */
class Uploader extends Component
{
    use WithFileUploads;

    /** @var list<TemporaryUploadedFile> */
    public array $files = [];

    public ?TemporaryUploadedFile $avatar = null;

    public function check(): void
    {
        $this->validate(['files' => 'required|array', 'files.*' => 'max:1']);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui::field>
                    <x-ui::file-upload wire:model="files" multiple accept="image/*" />
                </x-ui::field>
                <x-ui::field>
                    <x-ui::image-upload wire:model="avatar" src="/old.png" shape="circle" />
                </x-ui::field>
            </div>
            BLADE;
    }
}
