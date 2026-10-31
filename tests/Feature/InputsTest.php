<?php

use Esoboy\Ui\Facades\Ui;
use Esoboy\Ui\Support\Countdown;
use Illuminate\Support\Facades\Date;
use Livewire\Component;

/*
 * Tags Input, Number Input, Password Input, Verification Code Button, Countdown.
 */

it('renders tags as chips before the input, with a template for new ones', function () {
    $html = (string) $this->blade('<x-ui::tags-input :default-value="[\'php\', \'vue\']" name="tags" placeholder="Add" :max="3" class="w-80" />');

    expect($html)
        ->toContain('<eso-tags-input data-slot="tags-input" data-value="[&quot;php&quot;,&quot;vue&quot;]" data-max="3" data-name="tags" class="w-80">')
        ->toContain('<template data-tags-input-template>')
        ->toContain('data-tags-input-remove')
        ->toContain('<span data-slot="tags-input-tag" wire:key="tag-0"><span data-tags-input-label>php</span></span>')
        ->toContain('<input type="text" data-slot="tags-input-input" placeholder="Add" autocomplete="off"')
        ->toContain('<input type="hidden" name="tags[]" value="vue">');
});

it('puts its id and name on the input, where a label points', function () {
    $html = (string) $this->blade('<x-ui::number-input id="qty" aria-describedby="hint" wire:model="qty" />');

    expect($html)
        ->toContain('<eso-number-input data-slot="number-input" data-step="1" aria-describedby="hint" wire:model="qty">')
        ->toContain('autocomplete="off" id="qty"');
});

it('renders a number input as an input group with steppers', function () {
    $html = (string) $this->blade('<x-ui::number-input :default-value="1" :min="1" :max="10" step="0.5" name="qty" />');

    expect($html)
        ->toContain('<eso-number-input data-slot="number-input" data-min="1" data-max="10" data-step="0.5">')
        ->toContain('<div role="group" data-slot="input-group">')
        ->toMatch('/data-number-input="decrement" aria-label="Decrease" tabindex="-1" disabled="disabled"/')
        ->toContain('<input type="text" inputmode="decimal" role="spinbutton" data-slot="input-group-control" value="1"')
        ->toContain('name="qty"')
        ->toMatch('/aria-valuemin="1"\s+aria-valuemax="10"\s+aria-valuenow="1"/')
        ->toMatch('/data-number-input="increment" aria-label="Increase" tabindex="-1"\s*>/');
});

it('treats a non-numeric value as empty', function () {
    $this->blade('<x-ui::number-input default-value="abc" />')
        ->assertSee('value=""', false)
        ->assertDontSee('aria-valuenow', false);
});

it('renders a password input as an input group with an eye toggle', function () {
    $html = (string) $this->blade('<x-ui::password-input id="password" wire:model="password" placeholder="Password" class="w-64" />');

    expect($html)
        ->toContain('<eso-password-input data-slot="password-input" class="w-64">')
        ->toContain('<input type="password" data-slot="input-group-control" id="password" wire:model="password" placeholder="Password">')
        ->toMatch('/data-password-toggle="data-password-toggle" aria-pressed="false" aria-label="Show password"/')
        ->toContain('data-password-icon="hidden"')
        ->toContain('data-password-icon="shown"');
});

it('validates a password input inside a field', function () {
    $this->withViewErrors(['password' => 'Too short.'])
        ->blade('<x-ui::field><x-ui::password-input id="password" wire:model="password" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="password-error"', false)
        ->assertSeeInOrder(['</eso-password-input>', 'Too short.'], false);
});

it('renders a verification code button around a button', function () {
    $html = (string) $this->blade('<x-ui::verification-code-button name="sms" :seconds="30" wire:click="send">Send code</x-ui::verification-code-button>');

    expect($html)
        ->toContain('<eso-verification-code-button data-slot="verification-code-button" data-name="sms" data-seconds="30" data-label-countdown="Resend in :secondss" data-label-resend="Resend code">')
        ->toMatch('/data-variant="outline" data-size="default" wire:click="send">\s*<span data-verification-code-label>Send code<\/span>/');
});

it('renders a countdown with the time left, to a time or for seconds', function () {
    Date::setTestNow('2026-10-29 10:00:00');

    $this->blade('<x-ui::countdown until="2026-10-29 11:02:03" wire:finish="refresh" />')
        ->assertSee('<eso-countdown role="timer" data-slot="countdown" data-remaining="3723000" data-format="HH:mm:ss" wire:finish="refresh">01:02:03</eso-countdown>', false);

    $this->blade('<x-ui::countdown name="sale" :seconds="90" format="mm:ss" />')
        ->assertSee('data-remaining="90000" data-seconds="90" data-name="sale" data-format="mm:ss">01:30</eso-countdown>', false);

    $this->blade('<x-ui::countdown until="2026-10-29 09:00:00" />')
        ->assertSee('data-remaining="0"', false)
        ->assertSee('>00:00:00</eso-countdown>', false);

    Date::setTestNow();
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
    Livewire\Livewire::test(new class extends Component
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
