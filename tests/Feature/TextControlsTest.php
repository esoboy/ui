<?php

/*
 * Input, Textarea and Native Select share the same validation behaviour.
 */

it('renders an input', function () {
    $this->blade('<x-ui::input wire:model="email" placeholder="Email" />')
        ->assertSee('<input type="text" data-slot="input" wire:model="email" placeholder="Email">', false);
});

it('accepts an input type', function () {
    $this->blade('<x-ui::input type="email" />')->assertSee('type="email"', false);
});

it('renders a textarea with its content', function () {
    $this->blade('<x-ui::textarea name="bio">Hello</x-ui::textarea>')
        ->assertSee('<textarea data-slot="textarea" name="bio">Hello</textarea>', false);
});

it('renders a native select whose wrapper takes the class', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::native-select wire:model="role" class="w-full" size="sm">
            <x-ui::native-select.opt-group label="Staff">
                <x-ui::native-select.option value="admin">Admin</x-ui::native-select.option>
            </x-ui::native-select.opt-group>
        </x-ui::native-select>
        BLADE);

    $view->assertSee('<div data-slot="native-select-wrapper" data-size="sm" class="w-full">', false)
        ->assertSee('<select data-slot="native-select" data-size="sm" wire:model="role">', false)
        ->assertSee('<optgroup data-slot="native-select-optgroup" label="Staff">', false)
        ->assertSee('<option data-slot="native-select-option" value="admin">Admin</option>', false)
        ->assertSee('data-slot="native-select-icon"', false);
});

dataset('text controls', [
    'input' => ['<x-ui::input id="email" wire:model="email" />', '<input type="text" data-slot="input"'],
    'textarea' => ['<x-ui::textarea id="email" wire:model="email" />', '<textarea data-slot="textarea"'],
    'native select' => ['<x-ui::native-select id="email" wire:model="email" />', '<select data-slot="native-select" data-size="default"'],
]);

it('shows the validation error inside a field', function (string $control, string $opening) {
    $this->withViewErrors(['email' => 'The email field is required.'])
        ->blade("<x-ui::field>{$control}</x-ui::field>")
        ->assertSee($opening.' aria-invalid="true" aria-describedby="email-error" id="email" wire:model="email"', false)
        ->assertSeeInOrder([$opening, '<div role="alert" data-slot="field-error" id="email-error">', 'The email field is required.'], false);
})->with('text controls');

it('does not validate a control used on its own', function (string $control) {
    $this->withViewErrors(['email' => 'The email field is required.'])
        ->blade($control)
        ->assertDontSee('aria-invalid', false)
        ->assertDontSee('The email field is required.');
})->with('text controls');

it('keeps the control invalid but hides the message when the field says so', function (string $control) {
    $this->withViewErrors(['email' => 'The email field is required.'])
        ->blade("<x-ui::field :show-error=\"false\">{$control}</x-ui::field>")
        ->assertSee('aria-invalid="true"', false)
        ->assertDontSee('aria-describedby', false)
        ->assertDontSee('The email field is required.');
})->with('text controls');

it('stays clean without errors', function () {
    $this->withViewErrors(['other' => 'Nope.'])
        ->blade('<x-ui::field><x-ui::input wire:model="email" /></x-ui::field>')
        ->assertDontSee('aria-invalid', false)
        ->assertDontSee('field-error', false);
});
