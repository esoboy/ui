<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Component;

/**
 * Ui::editor('body')->insert($attachments) / ->insertContent($html) / ->focus(), for the
 * Editor of that name (its name, else the property its wire:model binds) in the
 * component calling: the same name in another component, another instance of it say,
 * is left alone.
 */
final class EditorHandle
{
    public function __construct(
        private readonly Component $component,
        private readonly string $name,
    ) {}

    /**
     * Inserts attachments at the cursor (from the app's own media library, say): each
     * ['url' => …] with any of name, mime (or type), size (bytes), alt, width, height
     * and poster. Images, audio and video show as themselves, the rest (and kinds the
     * format leaves out) as files.
     *
     * @param  array<array<string, mixed>>  $attachments
     */
    public function insert(array $attachments): void
    {
        $this->send('insert', attachments: array_values($attachments));
    }

    /**
     * Completes a media selection at its original position. An empty list cancels it.
     * Image, audio and video requests require the matching attachment MIME type.
     *
     * @param  array<array<string, mixed>>  $attachments
     */
    public function resolveMedia(string $requestId, array $attachments = []): void
    {
        $this->send('resolve-media', attachments: array_values($attachments), requestId: $requestId);
    }

    /**
     * Inserts HTML at the cursor (a template, a reply), as far as the format allows.
     */
    public function insertContent(string $html): void
    {
        $this->send('insert-content', html: $html);
    }

    public function focus(): void
    {
        $this->send('focus');
    }

    /**
     * @param  list<array<string, mixed>>  $attachments
     */
    private function send(string $action, array $attachments = [], ?string $html = null, ?string $requestId = null): void
    {
        $this->component->dispatch('ui-editor', name: $this->name, owner: $this->component->getId(), action: $action, attachments: $attachments, html: $html, requestId: $requestId);
    }
}
