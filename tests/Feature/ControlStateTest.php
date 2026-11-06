<?php

use Esoboy\Ui\Support\ControlState;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentAttributeBag;

function errorBag(array $messages, string $bag = 'default'): ViewErrorBag
{
    return (new ViewErrorBag)->put($bag, new MessageBag($messages));
}

it('reads the key from wire:model and its modifiers', function (string $attribute) {
    expect(ControlState::key(new ComponentAttributeBag([$attribute => 'form.email'])))->toBe('form.email');
})->with(['wire:model', 'wire:model.live', 'wire:model.live.debounce.500ms', 'wire:model.blur']);

it('reads the key from a name in dot notation', function (string $name, string $key) {
    expect(ControlState::key(new ComponentAttributeBag(['name' => $name])))->toBe($key);
})->with([
    ['email', 'email'],
    ['items[0][qty]', 'items.0.qty'],
    ['tags[]', 'tags'],
]);

it('prefers wire:model over name', function () {
    expect(ControlState::key(new ComponentAttributeBag(['name' => 'a', 'wire:model' => 'b'])))->toBe('b');
});

it('has no key without a binding', function () {
    expect(ControlState::key(new ComponentAttributeBag(['id' => 'x'])))->toBeNull();
});

it('collects unique messages for a key', function () {
    $errors = errorBag(['email' => ['Required.', 'Required.', 'Invalid.']]);

    expect(ControlState::messages($errors, 'email'))->toBe(['Required.', 'Invalid.']);
});

it('falls back to nested messages', function () {
    $errors = errorBag(['tags.0' => ['First tag is bad.'], 'tags.1' => ['Second tag is bad.']]);

    expect(ControlState::messages($errors, 'tags'))->toBe(['First tag is bad.', 'Second tag is bad.']);
});

it('reads from a named error bag', function () {
    $errors = errorBag(['email' => ['Taken.']], 'register');

    expect(ControlState::messages($errors, 'email'))->toBe([])
        ->and(ControlState::messages($errors, 'email', 'register'))->toBe(['Taken.']);
});

it('derives aria attributes and an error id', function () {
    $state = ControlState::for(new ComponentAttributeBag(['wire:model' => 'form.email']), errorBag(['form.email' => ['Required.']]));

    expect($state->errorId)->toBe('ui-form-email-error')
        ->and($state->attributes())->toBe(['aria-invalid' => 'true', 'aria-describedby' => 'ui-form-email-error']);
});

it('bases the error id on the control id', function () {
    $state = ControlState::for(new ComponentAttributeBag(['id' => 'email', 'name' => 'email']), errorBag(['email' => ['Required.']]));

    expect($state->errorId)->toBe('email-error');
});

it('stays invalid but undescribed when errors are hidden', function () {
    $state = ControlState::for(new ComponentAttributeBag(['name' => 'email']), errorBag(['email' => ['Required.']]), inField: true, showError: false);

    expect($state->showsError())->toBeFalse()
        ->and($state->attributes())->toBe(['aria-invalid' => 'true']);
});

it('ignores errors outside a field', function () {
    $state = ControlState::for(new ComponentAttributeBag(['name' => 'email']), errorBag(['email' => ['Required.']]), inField: false);

    expect($state->invalid())->toBeFalse()
        ->and($state->attributes())->toBe([]);
});
