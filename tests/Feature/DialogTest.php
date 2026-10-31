<?php

it('renders a dialog wired with invoker commands', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::dialog name="edit-profile">
            <x-ui::dialog.trigger variant="outline">Edit</x-ui::dialog.trigger>
            <x-ui::dialog.content class="sm:max-w-md">
                <x-ui::dialog.header>
                    <x-ui::dialog.title>Edit profile</x-ui::dialog.title>
                    <x-ui::dialog.description>Make changes.</x-ui::dialog.description>
                </x-ui::dialog.header>
                <x-ui::dialog.footer>
                    <x-ui::dialog.close variant="outline">Cancel</x-ui::dialog.close>
                </x-ui::dialog.footer>
            </x-ui::dialog.content>
        </x-ui::dialog>
        BLADE);

    $view->assertSee('<button type="button" data-slot="button" data-variant="outline" data-size="default" commandfor="edit-profile" command="show-modal">Edit</button>', false)
        ->assertSee('<dialog id="edit-profile" data-slot="dialog-content" aria-labelledby="edit-profile-title" aria-describedby="edit-profile-description" class="sm:max-w-md" wire:ignore.self>', false)
        ->assertSee('<div data-slot="dialog-header">', false)
        ->assertSee('<h2 id="edit-profile-title" data-slot="dialog-title">Edit profile</h2>', false)
        ->assertSee('<p id="edit-profile-description" data-slot="dialog-description">Make changes.</p>', false)
        ->assertSee('commandfor="edit-profile" command="close">Cancel</button>', false)
        ->assertSee('<button type="button" data-slot="button" data-variant="ghost" data-size="icon-sm" commandfor="edit-profile" command="close">', false)
        ->assertSee('<span class="sr-only">Close</span>', false);
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
        ->assertSee('data-variant="outline" data-size="default" commandfor="d" command="close">Close</button>', false);
});

it('renders an alert dialog without a close button', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::alert-dialog name="delete">
            <x-ui::alert-dialog.trigger>Delete</x-ui::alert-dialog.trigger>
            <x-ui::alert-dialog.content size="sm">
                <x-ui::alert-dialog.header>
                    <x-ui::alert-dialog.media>!</x-ui::alert-dialog.media>
                    <x-ui::alert-dialog.title>Sure?</x-ui::alert-dialog.title>
                    <x-ui::alert-dialog.description>Gone for good.</x-ui::alert-dialog.description>
                </x-ui::alert-dialog.header>
                <x-ui::alert-dialog.footer>
                    <x-ui::alert-dialog.cancel>Cancel</x-ui::alert-dialog.cancel>
                    <x-ui::alert-dialog.action variant="destructive" wire:click="delete">Delete</x-ui::alert-dialog.action>
                </x-ui::alert-dialog.footer>
            </x-ui::alert-dialog.content>
        </x-ui::alert-dialog>
        BLADE);

    $view->assertSee('commandfor="delete" command="show-modal">Delete</button>', false)
        ->assertSee('<dialog id="delete" role="alertdialog" data-slot="alert-dialog-content" data-size="sm" aria-labelledby="delete-title" aria-describedby="delete-description" wire:ignore.self>', false)
        ->assertSee('<div data-slot="alert-dialog-media">!</div>', false)
        ->assertSee('<h2 id="delete-title" data-slot="alert-dialog-title">Sure?</h2>', false)
        ->assertSee('data-variant="outline" data-size="default" commandfor="delete" command="close">Cancel</button>', false)
        ->assertSee('data-variant="destructive" data-size="default" commandfor="delete" command="close" wire:click="delete">Delete</button>', false)
        ->assertDontSee('alert-dialog-close', false);
});

it('renders a sheet on the requested side', function (string $side) {
    $this->blade("<x-ui::sheet name=\"s\"><x-ui::sheet.content side=\"{$side}\"><x-ui::sheet.title>T</x-ui::sheet.title></x-ui::sheet.content></x-ui::sheet>")
        ->assertSee("<dialog id=\"s\" data-slot=\"sheet-content\" data-side=\"{$side}\"", false)
        ->assertSee('<h2 id="s-title" data-slot="sheet-title">T</h2>', false)
        ->assertSee('data-variant="ghost" data-size="icon-sm" commandfor="s" command="close"', false);
})->with(['top', 'right', 'bottom', 'left']);

it('defaults a sheet to the right', function () {
    $this->blade('<x-ui::sheet name="s"><x-ui::sheet.content>x</x-ui::sheet.content></x-ui::sheet>')
        ->assertSee('data-side="right"', false);
});
