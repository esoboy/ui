<?php

use Esoboy\Ui\Tests\Fixtures\ProfileForm;
use Livewire\Livewire;

it('renders clean controls before validation', function () {
    Livewire::test(ProfileForm::class)
        ->assertDontSeeHtml('aria-invalid')
        ->assertDontSeeHtml('data-slot="field-error"');
});

it('shows Livewire validation errors under each bound control', function () {
    Livewire::test(ProfileForm::class)
        ->call('save')
        ->assertHasErrors(['email', 'plan', 'terms'])
        ->assertSeeHtml('<input type="text" data-slot="input" aria-invalid="true" aria-describedby="email-error" id="email" wire:model="email">')
        ->assertSeeHtml('<div role="alert" data-slot="field-error" id="email-error">')
        ->assertSee('The email field is required.')
        ->assertSee('The plan field is required.')
        ->assertDontSee('The terms field must be accepted.');
});

it('clears errors once the data is valid', function () {
    Livewire::test(ProfileForm::class)
        ->call('save')
        ->set('email', 'dev@example.com')
        ->set('plan', 'pro')
        ->set('terms', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDontSeeHtml('data-slot="field-error"');
});
