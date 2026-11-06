<?php

use Carbon\Carbon;
use Esoboy\Ui\Livewire\DatePicker;
use Esoboy\Ui\Livewire\TimePicker;
use Esoboy\Ui\Support\DatePresets;
use Livewire\Livewire;

it('renders localized calendar dates and constraints in PHP', function () {
    app()->setLocale('zh_CN');
    $html = (string) $this->blade('<x-ui::calendar mode="range" month="2026-01" :value="[\'from\' => \'2026-01-05\', \'to\' => null]" caption-layout="dropdown" :number-of-months="2" :show-outside-days="false" :week-starts-on="1" min="2025-01-01" max="2027-12-31" :disabled-dates="[\'2026-01-10\']" class="rounded-lg border" />');
    expect($html)->toContainMarkup('<eso-calendar data-slot="calendar" data-mode="range" class="rounded-lg border">')
        ->toContain('data-day="2026-01-05"')->toContain('data-calendar-previous')->toContain('选择月份')->toContain('data-hidden="true"')
        ->not->toContain('<template')->and(substr_count($html, 'data-slot="calendar-month"'))->toBe(2);
});

it('renders a labelled single calendar without client date generation', function () {
    $this->blade('<x-ui::calendar value="2026-01-05" />')->assertSee('data-mode="single"', false)->assertSee('January 2026')->assertSee('data-day="2026-01-05"', false);
});

it('renders the selected picker label and popover on the server', function () {
    $html = (string) $this->blade('<x-ui::date-picker value="2026-01-05" placeholder="Pick a date" class="w-64" name="date" />');
    expect($html)->toContainMarkup('<span data-slot="date-picker-value">January 5, 2026</span>')->toContain('data-date-picker-content')->toContain('class="w-64"')->toContainMarkup('<input type="hidden" name="date" value="2026-01-05">');
});

it('leads a range picker with the calendar icon and renders two months', function () {
    $html = (string) $this->blade('<x-ui::date-picker mode="range" month="2026-01" placeholder="Pick a range" />');
    expect($html)->toContain('data-icon="inline-start"')->not->toContain('data-icon="inline-end"')->and(substr_count($html, 'data-slot="calendar-month"'))->toBe(2);
});

it('normalizes Carbon presets and selects them in PHP', function () {
    $presets = DatePresets::normalize(['Week' => [Carbon::parse('2026-10-21'), '2026-10-27 12:00:00']], 'range');
    expect($presets)->toBe(['Week' => ['from' => '2026-10-21', 'to' => '2026-10-27']]);
    Livewire::test(DatePicker::class, ['settings' => ['presets' => $presets, 'mode' => 'range']])->call('preset', 'Week')->assertSet('value', $presets['Week']);
});

it('renders the shared time options with PHP generated labels', function () {
    $html = (string) $this->blade('<x-ui::date-picker time value="2026-01-05 14:30" />');
    expect($html)->toContain('data-date-picker-time="from"')
        ->toContainMarkup('<button wire:key="time-from-hour-14" aria-pressed="true">')
        ->toContainMarkup('<button wire:key="time-from-minute-30" aria-pressed="true">')
        ->not->toContain('type="time"', 'data-date-picker-time="to"');
});

it('keeps separate times at each end of a range', function () {
    app()->setLocale('zh_CN');
    $html = (string) $this->blade('<x-ui::date-picker mode="range" time step="1" time-value="09:00" :value="[\'from\' => \'2026-01-05 08:00:00\', \'to\' => null]" />');
    expect($html)->toContain('开始时间', '结束时间', 'aria-label="秒"')
        ->toContainMarkup('<button wire:key="time-from-hour-8" aria-pressed="true">')
        ->toContainMarkup('<button wire:key="time-to-hour-9" aria-pressed="true">');

    Livewire::test(DatePicker::class, ['settings' => ['mode' => 'range', 'time' => true, 'step' => 1, 'timeValue' => '09:00']])
        ->call('pickTime', 'to', 'hour', 17)
        ->call('pickTime', 'to', 'second', 5)
        ->call('select', '2026-01-05')
        ->call('select', '2026-01-06')
        ->assertSet('value', ['from' => '2026-01-05 09:00:00', 'to' => '2026-01-06 17:00:05']);
});

it('applies a common time to the selected dates in multiple mode', function () {
    Livewire::test(DatePicker::class, ['settings' => ['mode' => 'multiple', 'time' => true]])
        ->call('select', '2026-01-05')
        ->call('select', '2026-01-06')
        ->call('pickTime', 'from', 'hour', 15)
        ->assertSet('value', ['2026-01-05 15:00', '2026-01-06 15:00'])
        ->call('select', '2026-01-05')
        ->assertSet('value', ['2026-01-06 15:00']);
});

it('renders an outline time picker with named form value and optional seconds', function () {
    $html = (string) $this->blade('<x-ui::time-picker id="time" name="time" value="08:15:30" step="1" />');
    expect($html)->toContainMarkup('<button id="time" data-variant="outline">')
        ->toContainMarkup('<input type="hidden" name="time" value="08:15:30">')
        ->toContainMarkup('<button wire:key="time-value-second-30" aria-pressed="true">')
        ->not->toContain('type="time"', 'wire:click="clear"');
});

it('enforces time boundaries and steps when picking a segment', function () {
    Livewire::test(TimePicker::class, ['settings' => ['min' => '09:15', 'max' => '11:30', 'step' => 900]])
        ->call('pickTime', 'hour', 8)->assertSet('value', null)
        ->call('pickTime', 'hour', 10)->assertSet('value', '10:15')
        ->call('pickTime', 'minute', 45)->assertSet('value', '10:45')
        ->call('pickTime', 'hour', 11)->assertSet('value', '11:00')
        ->call('pickTime', 'minute', 45)->assertSet('value', '11:00');
});

it('renders and accepts only combined time options inside the interval and boundaries', function () {
    Livewire::test(TimePicker::class, [
        'settings' => ['mode' => 'combined', 'min' => '09:05', 'max' => '10:20', 'step' => 300],
        'value' => '09:05',
    ])->assertSeeHtml('data-time-option="09:10"')
        ->assertDontSeeHtml('data-time-option="09:06"')
        ->call('showTimeHour', 10)
        ->assertSet('value', '09:05')
        ->assertSeeHtml('data-time-option="10:20"')
        ->assertDontSeeHtml('data-time-option="10:25"')
        ->call('pickTime', 'combined', 10 * 3600 + 25 * 60)->assertSet('value', '09:05')
        ->call('pickTime', 'combined', 10 * 3600 + 12 * 60)->assertSet('value', '09:05')
        ->call('pickTime', 'combined', 10 * 3600 + 15 * 60)->assertSet('value', '10:15');
});

it('navigates combined time choices by available hours and stops at the boundaries', function () {
    $picker = Livewire::test(TimePicker::class, [
        'settings' => ['mode' => 'combined', 'min' => '09:00', 'max' => '11:00', 'step' => 7200],
        'value' => '09:00',
    ])->assertSeeHtml('data-time-option="09:00"')
        ->assertSeeHtml('data-time-hour-choice="9"')
        ->assertSeeHtml('data-time-hour-choice="11"')
        ->assertDontSeeHtml('data-time-hour-choice="10"')
        ->assertDontSeeHtml('<select');

    expect($picker->html())
        ->toContainMarkup('<button aria-label="Go to the previous available hour" disabled="disabled">')
        ->toContainMarkup('<button aria-label="Go to the next available hour" wire:click="showTimeHour(11)">');

    $picker->call('showTimeHour', 11)->assertSet('value', '09:00')->assertSeeHtml('data-time-option="11:00"');

    expect($picker->html())
        ->toContainMarkup('<button aria-label="Go to the previous available hour" wire:click="showTimeHour(9)">')
        ->toContainMarkup('<button aria-label="Go to the next available hour" disabled="disabled">');
});

it('prevents changes to disabled and read-only time pickers', function (string $state) {
    Livewire::test(TimePicker::class, ['settings' => [$state => true], 'value' => '10:30'])
        ->call('pickTime', 'hour', 11)
        ->assertSet('value', '10:30');
})->with(['disabled', 'readonly']);

it('keeps the time picker validation state on its trigger', function () {
    $this->withViewErrors(['time' => 'Pick a time.'])
        ->blade('<x-ui::field><x-ui::time-picker id="time" wire:model="time" /></x-ui::field>')
        ->assertSeeMarkup('<button id="time" aria-invalid="true" aria-describedby="time-error">')
        ->assertSeeMarkupInOrder(['</eso-time-picker>', 'Pick a time.']);
});

it('keeps the picker validation error with its outer field', function () {
    $this->withViewErrors(['date' => 'Pick a date.'])->blade('<x-ui::field><x-ui::date-picker id="date" wire:model="date" /></x-ui::field>')->assertSee('aria-invalid="true"', false)->assertSee('aria-describedby="date-error"', false)->assertSeeMarkupInOrder(['</eso-date-picker>', 'Pick a date.']);
});
