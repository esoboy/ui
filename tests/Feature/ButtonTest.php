<?php

use Illuminate\Support\Facades\Blade;

it('renders a button with shadcn defaults', function () {
    $this->blade('<x-ui::button>Save</x-ui::button>')
        ->assertSee('<button type="button" data-slot="button" data-variant="default" data-size="default">Save</button>', false);
});

it('reflects variant and size as data attributes', function () {
    $this->blade('<x-ui::button variant="outline" size="sm">Save</x-ui::button>')
        ->assertSee('data-variant="outline"', false)
        ->assertSee('data-size="sm"', false);
});

it('resolves dynamic variant and size at runtime', function () {
    $this->blade('<x-ui::button :variant="$variant" :size="$size">Save</x-ui::button>', ['variant' => 'destructive', 'size' => 'lg'])
        ->assertSee('data-variant="destructive"', false)
        ->assertSee('data-size="lg"', false);
});

it('accepts a submit type', function () {
    $this->blade('<x-ui::button type="submit">Save</x-ui::button>')
        ->assertSee('type="submit"', false);
});

it('renders an anchor when given an href', function () {
    $view = $this->blade('<x-ui::button href="/settings" variant="link">Settings</x-ui::button>');

    $view->assertSee('<a data-slot="button" data-variant="link" data-size="default" href="/settings">Settings</a>', false);
    $view->assertDontSee('<button', false);
});

it('renders an anchor for a dynamic href', function () {
    $this->blade('<x-ui::button :href="$url">Settings</x-ui::button>', ['url' => '/settings'])
        ->assertSee('<a data-slot="button" data-variant="default" data-size="default" href="/settings">Settings</a>', false);
});

it('passes classes and Livewire directives through untouched', function () {
    $this->blade('<x-ui::button class="rounded-full px-8" wire:click="save" disabled>Save</x-ui::button>')
        ->assertSee('class="rounded-full px-8"', false)
        ->assertSee('wire:click="save"', false)
        ->assertSee('disabled', false);
});

it('folds a static button into plain html at compile time', function () {
    $compiled = Blade::compileString('<x-ui::button variant="secondary">Save</x-ui::button>');

    expect($compiled)
        ->toContain('data-variant="secondary"')
        ->not->toContain('x-ui::button');
});
