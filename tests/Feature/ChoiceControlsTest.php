<?php

/*
 * Checkbox, Switch and Radio Group are native inputs, so wire:model binds directly.
 */

it('renders a checkbox', function () {
    $this->blade('<x-ui::checkbox id="terms" wire:model="terms" />')
        ->assertSeeMarkup('<input type="checkbox" data-slot="checkbox" id="terms" wire:model="terms">');
});

it('shows the error of a single checkbox inside a field', function () {
    $this->withViewErrors(['terms' => 'The terms field must be accepted.'])
        ->blade('<x-ui::field orientation="horizontal"><x-ui::checkbox id="terms" wire:model="terms" /></x-ui::field>')
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('The terms field must be accepted.');
});

it('leaves the error of a checkbox set to field.error', function () {
    $view = $this->withViewErrors(['channels' => 'Pick one.'])
        ->blade(<<<'BLADE'
            <x-ui::field.set>
                <x-ui::field orientation="horizontal"><x-ui::checkbox value="email" wire:model="channels" /></x-ui::field>
                <x-ui::field orientation="horizontal"><x-ui::checkbox value="sms" wire:model="channels" /></x-ui::field>
                <x-ui::field.error name="channels" />
            </x-ui::field.set>
            BLADE);

    expect(substr_count($view, 'aria-invalid="true"'))->toBe(2)
        ->and(substr_count($view, 'Pick one.'))->toBe(1);
});

it('renders a switch', function () {
    $this->blade('<x-ui::switch wire:model="notify" size="sm" />')
        ->assertSeeMarkup('<input type="checkbox" role="switch" data-slot="switch" data-size="sm" wire:model="notify">');
});

it('shows switch errors inside a field', function () {
    $this->withViewErrors(['notify' => 'Required.'])
        ->blade('<x-ui::field><x-ui::switch wire:model="notify" /></x-ui::field>')
        ->assertSee('Required.');
});

it('does not validate choices used on their own', function (string $blade) {
    $this->withViewErrors(['terms' => 'Required.'])
        ->blade($blade)
        ->assertDontSee('aria-invalid', false)
        ->assertDontSee('Required.');
})->with([
    'checkbox' => ['<x-ui::checkbox wire:model="terms" />'],
    'switch' => ['<x-ui::switch wire:model="terms" />'],
    'radio group' => ['<x-ui::radio-group wire:model="terms"><x-ui::radio-group.item value="1" /></x-ui::radio-group>'],
]);

it('forwards a radio group binding to its native radios', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::radio-group wire:model="plan">
            <x-ui::radio-group.item value="starter" />
            <x-ui::radio-group.item value="pro" />
        </x-ui::radio-group>
        BLADE);

    $view->assertSeeMarkup('<div role="radiogroup" data-slot="radio-group">')
        ->assertSeeMarkup('<input type="radio" data-slot="radio-group-item" value="starter" wire:model="plan">')
        ->assertSeeMarkup('<input type="radio" data-slot="radio-group-item" value="pro" wire:model="plan">');
});

it('checks the item matching the default value', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::radio-group name="plan" value="pro">
            <x-ui::radio-group.item value="starter" />
            <x-ui::radio-group.item value="pro" />
        </x-ui::radio-group>
        BLADE);

    $view->assertSeeMarkup('<input value="starter">')
        ->assertSeeMarkup('<input value="pro" checked="checked">');
});

it('shows the radio group error once, after the group in a field set', function () {
    $view = $this->withViewErrors(['plan' => 'Choose a plan.'])
        ->blade(<<<'BLADE'
            <x-ui::field.set>
                <x-ui::radio-group wire:model="plan">
                    <x-ui::field orientation="horizontal"><x-ui::radio-group.item value="starter" /></x-ui::field>
                    <x-ui::field orientation="horizontal"><x-ui::radio-group.item value="pro" /></x-ui::field>
                </x-ui::radio-group>
            </x-ui::field.set>
            BLADE);

    $view->assertSee('aria-invalid="true"', false)
        ->assertSeeMarkupInOrder(['</div>', 'Choose a plan.']);

    expect(substr_count($view, 'Choose a plan.'))->toBe(1);
});
