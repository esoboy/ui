<?php

use Esoboy\Ui\Facades\Ui;
use Esoboy\Ui\Support\Countdown;
use Illuminate\Support\Facades\Date;
use Livewire\Component;
use Livewire\Livewire;

it('steps decimal numbers on the server without exceeding the configured bounds', function () {
    Livewire::test('ui.number-input', [
        'value' => 0.9, 'settings' => ['min' => 0, 'max' => 1, 'step' => 0.1],
    ])
        ->call('step', 1)->assertSet('value', 1.0)
        ->call('step', 1)->assertSet('value', 1.0)
        ->call('step', -1)->assertSet('value', 0.9);
});

/*
 * Tags Input, Number Input, Password Input, Verification Code Button, Countdown.
 */

it('renders tags as server-rendered chips and native form values', function () {
    $html = (string) $this->blade('<x-ui::tags-input :value="[\'php\', \'vue\']" name="tags" placeholder="Add" :max="3" class="w-80" />');

    expect($html)
        ->toContainMarkup('<eso-tags-input data-slot="tags-input" class="w-80">')
        ->not->toContain('<template')
        ->toContain('data-tags-input-remove')
        ->toContainMarkup('<span data-tags-input-label>php</span>')
        ->toContainMarkup('<input type="text" data-slot="tags-input-input" placeholder="Add" autocomplete="off">')
        ->toContainMarkup('<input type="hidden" name="tags[]" value="vue">');
});

it('puts its id and name on the input, where a label points', function () {
    $html = (string) $this->blade('<x-ui::number-input id="qty" aria-describedby="hint" wire:model="qty" />');

    expect($html)
        ->toContainMarkup('<eso-number-input data-slot="number-input" data-step="1" aria-describedby="hint">')
        ->toContainMarkup('<input autocomplete="off" id="qty" wire:model.live.debounce.250ms.number="value">');
});

it('renders a number input as an input group with steppers', function () {
    $html = (string) $this->blade('<x-ui::number-input :value="1" :min="1" :max="10" step="0.5" name="qty" />');

    expect($html)
        ->toContainMarkup('<eso-number-input data-slot="number-input" data-min="1" data-max="10" data-step="0.5">')
        ->toContainMarkup('<div role="group" data-slot="input-group">')
        ->toMatch('/data-number-input="decrement" aria-label="Decrease" tabindex="-1" disabled="disabled"/')
        ->toContainMarkup('<input type="text" data-slot="input-group-control" inputmode="decimal" role="spinbutton" value="1">')
        ->toContain('name="qty"')
        ->toMatch('/aria-valuemin="1"\s+aria-valuemax="10"\s+aria-valuenow="1"/')
        ->toContainMarkup('<button data-number-input="increment" aria-label="Increase" tabindex="-1" wire:click="step(1)">');
});

it('treats a non-numeric value as empty', function () {
    $this->blade('<x-ui::number-input value="abc" />')
        ->assertSee('value=""', false)
        ->assertDontSee('aria-valuenow', false);
});

it('renders a password input as an input group with an eye toggle', function () {
    $html = (string) $this->blade('<x-ui::password-input id="password" wire:model="password" placeholder="Password" class="w-64" />');

    expect($html)
        ->toContainMarkup('<eso-password-input data-slot="password-input" class="w-64">')
        ->toContainMarkup('<input type="password" data-slot="input-group-control" id="password" wire:model="password" placeholder="Password">')
        ->toMatch('/data-password-toggle="data-password-toggle" aria-pressed="false" aria-label="Show password"/')
        ->toContain('data-password-icon="hidden"')
        ->toContain('data-password-icon="shown"');
});

it('validates a password input inside a field', function () {
    $this->withViewErrors(['password' => 'Too short.'])
        ->blade('<x-ui::field><x-ui::password-input id="password" wire:model="password" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="password-error"', false)
        ->assertSeeMarkupInOrder(['</eso-password-input>', 'Too short.']);
});

it('keeps a compound inputs validation on its bound value and shows one error', function (string $component) {
    $html = (string) $this->withViewErrors(['form.value' => 'Bound value is invalid.', 'other' => 'Wrong field.'])
        ->blade('<x-ui::field><x-ui::'.$component.' id="value" name="other" wire:model="form.value" /></x-ui::field>');

    expect($html)
        ->toContain('aria-invalid="true" aria-describedby="value-error"')
        ->not->toContain('Wrong field.')
        ->and(substr_count($html, 'Bound value is invalid.'))->toBe(1);

    expect(strpos($html, 'Bound value is invalid.'))->toBeGreaterThan(strpos($html, '</eso-'.$component.'>'));

    $this->withViewErrors(['form.value' => 'Bound value is invalid.'])
        ->blade('<x-ui::field :show-error="false"><x-ui::'.$component.' wire:model="form.value" /></x-ui::field>')
        ->assertSee('aria-invalid="true"', false)
        ->assertDontSee('Bound value is invalid.');
})->with(['password-input', 'number-input']);

it('renders a verification code button around a button', function () {
    $html = (string) $this->blade('<x-ui::verification-code-button name="sms" :seconds="30" wire:click="send">Send code</x-ui::verification-code-button>');

    expect($html)
        ->toContainMarkup('<eso-verification-code-button data-slot="verification-code-button">')
        ->toContainMarkup('<button data-variant="outline" data-size="default" wire:click="send"><span data-verification-code-label>Send code</span>');
});

it('renders a countdown with the time left, to a time or for seconds', function () {
    Date::setTestNow('2026-10-29 10:00:00');

    $this->blade('<x-ui::countdown until="2026-10-29 11:02:03" wire:finish="refresh" />')
        ->assertSeeMarkup('<eso-countdown role="timer" data-slot="countdown" wire:finish="refresh">')
        ->assertSee('01:02:03');

    $this->blade('<x-ui::countdown name="sale" :seconds="90" format="mm:ss" />')
        ->assertSee('01:30');

    $this->blade('<x-ui::countdown until="2026-10-29 09:00:00" />')
        ->assertSee('00:00:00');

    Date::setTestNow();
});

it('renders the countdown from a deadline and only dispatches finish once per start', function () {
    Date::setTestNow('2026-10-29 10:00:00');

    try {
        $timer = Livewire::test('ui.countdown', ['verification' => true, 'seconds' => 5])
            ->assertDontSee('Resend code')
            ->call('start')
            ->assertSee('Resend in 5s');
        Date::setTestNow('2026-10-29 10:00:06');
        $timer->call('tick')->assertSee('Resend code')->assertDispatched('finish')
            ->call('tick')->assertNotDispatched('finish')
            ->call('start', 3)->assertSee('Resend in 3s');
    } finally {
        Date::setTestNow();
    }
});

it('formats countdowns, the largest unit taking all above it', function (int $seconds, string $format, string $text) {
    expect(Countdown::format($seconds * 1000, $format))->toBe($text);
})->with([
    [90061, 'HH:mm:ss', '25:01:01'],
    [90061, 'D [days] HH:mm:ss', '1 days 01:01:01'],
    [90061, 'D天H时m分s秒', '1天1时1分1秒'],
    [61, 'ss', '61'],
    [0, 'mm:ss', '00:00'],
]);

it('rounds a countdown up to the second it is in', function () {
    expect(Countdown::format(1500, 'ss'))->toBe('02')
        ->and(Countdown::format(1000, 'ss'))->toBe('01');
});

it('starts countdowns from Livewire', function () {
    Livewire::test(new class extends Component
    {
        public function send(): void
        {
            Ui::countdown('sms', 30);
        }

        public function render(): string
        {
            return '<div></div>';
        }
    })
        ->call('send')
        ->assertDispatched('ui-countdown', name: 'sms', seconds: 30);
});
