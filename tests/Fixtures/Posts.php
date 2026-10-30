<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Esoboy\Ui\Facades\Ui;
use Livewire\Component;

class Posts extends Component
{
    public string $body = '';

    public ?string $saved = null;

    // As an app loading a post would: set on the server.
    public function load(string $html): void
    {
        $this->body = $html;
    }

    // As an app stores what the Editor sends back: sanitized to its format first.
    public function save(): void
    {
        $this->saved = Ui::sanitize($this->body);
    }

    // As an app's own attachment library would insert.
    public function attach(): void
    {
        Ui::editor('body')->insert([['url' => '/a.png', 'name' => 'a.png', 'mime' => 'image/png']]);
    }

    public function template(): void
    {
        Ui::editor('body')->insertContent('<p>Hello</p>');
    }

    public function render(): string
    {
        return '<div><x-ui::editor wire:model="body" /></div>';
    }
}
