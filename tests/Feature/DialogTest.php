<?php

it('attaches dialog behavior to the supplied controls without wrapping them in buttons', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::dialog name="edit-details">
            <x-ui::dialog.trigger aria-label="Edit details">
                <button id="my-edit" type="button"><strong>Edit</strong></button>
            </x-ui::dialog.trigger>
            <x-ui::dialog.content :show-close-button="false">
                <x-ui::dialog.close>
                    <x-ui::button id="my-cancel" variant="outline">Cancel</x-ui::button>
                </x-ui::dialog.close>
            </x-ui::dialog.content>
        </x-ui::dialog>
        BLADE);

    expect(substr_count($html, '<button'))->toBe(2);
    expect($html)
        ->toContainMarkup('<button id="my-edit" aria-label="Edit details" commandfor="edit-details" command="show-modal"><strong>Edit</strong></button>')
        ->toContainMarkup('<button id="my-cancel" data-variant="outline" commandfor="edit-details" command="close">Cancel</button>');
});

it('renders a dialog wired with invoker commands', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::dialog name="edit-profile">
            <x-ui::dialog.trigger><x-ui::button variant="outline">Edit</x-ui::button></x-ui::dialog.trigger>
            <x-ui::dialog.content class="sm:max-w-md">
                <x-ui::dialog.header>
                    <x-ui::dialog.title>Edit profile</x-ui::dialog.title>
                    <x-ui::dialog.description>Make changes.</x-ui::dialog.description>
                </x-ui::dialog.header>
                <x-ui::dialog.footer>
                    <x-ui::dialog.close><x-ui::button variant="outline">Cancel</x-ui::button></x-ui::dialog.close>
                </x-ui::dialog.footer>
            </x-ui::dialog.content>
        </x-ui::dialog>
        BLADE);

    $view->assertSeeMarkup('<button type="button" data-slot="button" data-variant="outline" data-size="default" commandfor="edit-profile" command="show-modal">Edit</button>')
        ->assertSeeMarkup('<dialog id="edit-profile" data-slot="dialog-content" aria-labelledby="edit-profile-title" aria-describedby="edit-profile-description" class="sm:max-w-md" wire:ignore.self>')
        ->assertSeeMarkup('<div data-slot="dialog-header">')
        ->assertSeeMarkup('<h2 id="edit-profile-title" data-slot="dialog-title">Edit profile</h2>')
        ->assertSeeMarkup('<p id="edit-profile-description" data-slot="dialog-description">Make changes.</p>')
        ->assertSeeMarkup('commandfor="edit-profile" command="close">Cancel</button>')
        ->assertSeeMarkup('<button type="button" data-slot="button" data-variant="ghost" data-size="icon-sm" commandfor="edit-profile" command="close">')
        ->assertSeeMarkup('<span class="sr-only">Close</span>');
});

it('can hide the close button and add one to the footer', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::dialog name="d">
            <x-ui::dialog.content :show-close-button="false">
                <x-ui::dialog.footer show-close-button />
            </x-ui::dialog.content>
        </x-ui::dialog>
        BLADE);

    $view->assertDontSee('data-variant="ghost" data-size="icon-sm"', false)
        ->assertSeeMarkup('data-variant="outline" data-size="default" commandfor="d" command="close">Close</button>');
});

it('renders an alert dialog without a close button', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::alert-dialog name="delete">
            <x-ui::alert-dialog.trigger><x-ui::button>Delete</x-ui::button></x-ui::alert-dialog.trigger>
            <x-ui::alert-dialog.content size="sm">
                <x-ui::alert-dialog.header>
                    <x-ui::alert-dialog.media>!</x-ui::alert-dialog.media>
                    <x-ui::alert-dialog.title>Sure?</x-ui::alert-dialog.title>
                    <x-ui::alert-dialog.description>Gone for good.</x-ui::alert-dialog.description>
                </x-ui::alert-dialog.header>
                <x-ui::alert-dialog.footer>
                    <x-ui::alert-dialog.cancel><x-ui::button variant="outline">Cancel</x-ui::button></x-ui::alert-dialog.cancel>
                    <x-ui::alert-dialog.action><x-ui::button variant="destructive" wire:click="delete">Delete</x-ui::button></x-ui::alert-dialog.action>
                </x-ui::alert-dialog.footer>
            </x-ui::alert-dialog.content>
        </x-ui::alert-dialog>
        BLADE);

    $view->assertSeeMarkup('commandfor="delete" command="show-modal">Delete</button>')
        ->assertSeeMarkup('<dialog id="delete" role="alertdialog" data-slot="alert-dialog-content" data-size="sm" aria-labelledby="delete-title" aria-describedby="delete-description" wire:ignore.self>')
        ->assertSeeMarkup('<div data-slot="alert-dialog-media">!</div>')
        ->assertSeeMarkup('<h2 id="delete-title" data-slot="alert-dialog-title">Sure?</h2>')
        ->assertSeeMarkup('data-variant="outline" data-size="default" commandfor="delete" command="close">Cancel</button>')
        ->assertSeeMarkup('<button data-variant="destructive" data-size="default" commandfor="delete" command="close" wire:click="delete">Delete</button>')
        ->assertDontSee('alert-dialog-close', false);
});

it('renders a sheet on the requested side', function (string $side) {
    $this->blade("<x-ui::sheet name=\"s\"><x-ui::sheet.content side=\"{$side}\"><x-ui::sheet.title>T</x-ui::sheet.title></x-ui::sheet.content></x-ui::sheet>")
        ->assertSeeMarkup("<dialog id=\"s\" data-slot=\"sheet-content\" data-side=\"{$side}\">")
        ->assertSeeMarkup('<h2 id="s-title" data-slot="sheet-title">T</h2>')
        ->assertSee('data-variant="ghost" data-size="icon-sm" commandfor="s" command="close"', false);
})->with(['top', 'right', 'bottom', 'left']);

it('defaults a sheet to the right', function () {
    $this->blade('<x-ui::sheet name="s"><x-ui::sheet.content>x</x-ui::sheet.content></x-ui::sheet>')
        ->assertSee('data-side="right"', false);
});
