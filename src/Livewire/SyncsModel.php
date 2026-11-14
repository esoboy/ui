<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Attributes\Locked;

/**
 * Recompute derived UI fields when a modelable parent replaces the value.
 * Livewire seeds the value during hydration without invoking updatedValue.
 * Only a fingerprint is retained; the parent remains the source of the value.
 */
trait SyncsModel
{
    #[Locked]
    public string $modelFingerprint = '';

    public function hydrateSyncsModel(): void
    {
        if ($this->modelFingerprint !== hash('sha256', serialize($this->value))) {
            $this->modelChanged();
        }
    }

    public function dehydrateSyncsModel(): void
    {
        $this->modelFingerprint = hash('sha256', serialize($this->value));
    }

    public function updatedValue(): void
    {
        $this->modelChanged();
    }

    abstract protected function modelChanged(): void;
}
