<?php

it('renders a calendar shell configured for the client', function () {
    app()->setLocale('zh_CN');

    $this->blade(<<<'BLADE'
        <x-ui::calendar mode="range" :default-value="['from' => '2026-01-05', 'to' => null]" default-month="2026-01"
            caption-layout="dropdown" :number-of-months="2" :show-outside-days="false" :week-starts-on="1"
            min="2025-01-01" max="2027-12-31" :disabled-dates="['2026-01-10']" class="rounded-lg border" />
        BLADE)
        ->assertSee('<eso-calendar data-slot="calendar" data-mode="range" data-value="{&quot;from&quot;:&quot;2026-01-05&quot;,&quot;to&quot;:null}" data-default-month="2026-01" data-caption-layout="dropdown" data-number-of-months="2" data-show-outside-days="false" data-week-starts-on="1" data-min="2025-01-01" data-max="2027-12-31" data-disabled-dates="[&quot;2026-01-10&quot;]" data-locale="zh-CN" class="rounded-lg border" wire:ignore>', false)
        ->assertSeeInOrder([
            '<template data-template="nav">',
            'data-calendar-previous="data-calendar-previous" aria-label="上个月"><svg', '<path d="m15 18-6-6 6-6"/>',
            'data-calendar-next="data-calendar-next" aria-label="下个月"><svg',
            '<template data-template="month">', '<div data-slot="calendar-month">',
            '<template data-template="dropdown-month">', '<select data-calendar-month aria-label="选择月份"></select>',
            '<template data-template="dropdown-year">', '<select data-calendar-year aria-label="选择年份"></select>',
            '<template data-template="day"><td data-slot="calendar-day" role="gridcell"><button type="button" data-slot="button" data-variant="ghost" data-size="icon" data-day="data-day" tabindex="-1"></button>',
            '</eso-calendar>',
        ], false);
});

it('defaults to a single, labelled calendar', function () {
    $this->blade('<x-ui::calendar wire:model="date" />')
        ->assertSee('data-mode="single"', false)
        ->assertSee('data-caption-layout="label"', false)
        ->assertSee('data-show-outside-days="true"', false)
        ->assertDontSee('data-value', false)
        ->assertSee('wire:model="date"', false);
});

it('renders a date picker around a popover and a calendar', function () {
    $html = (string) $this->blade('<x-ui::date-picker wire:model="date" placeholder="Pick a date" default-value="2026-01-05" mode="single" class="w-64" />');

    expect($html)
        ->toContain('<eso-date-picker data-slot="date-picker" data-mode="single" data-placeholder="Pick a date" data-locale="en" wire:model="date">')
        ->toContain('data-variant="outline" data-size="default" data-popover-trigger="data-popover-trigger" command="toggle-popover" commandfor="" data-date-picker-trigger="data-date-picker-trigger"')
        ->toContain('class="w-64"')
        ->toContain('<span data-slot="date-picker-value">Pick a date</span>')
        ->toContain('data-date-picker-content')
        ->toContain('<eso-calendar data-slot="calendar" data-mode="single" data-value="&quot;2026-01-05&quot;"');
});

it('leads a range picker with a calendar icon and shows two months', function () {
    $html = (string) $this->blade('<x-ui::date-picker mode="range" placeholder="Pick a range" />');

    expect($html)
        ->toMatch('/data-date-picker-trigger="data-date-picker-trigger">\s*<svg [^>]*data-icon="inline-start"/')
        ->not->toContain('data-icon="inline-end"')
        ->toContain('data-number-of-months="2"');
});

it('renders presets under the calendar as calendar values', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::date-picker mode="range" :presets="['Last 7 days' => [\Illuminate\Support\Carbon::parse('2026-10-21'), '2026-10-27 12:00:00'], 'Fixed' => ['from' => '2026-01-01', 'to' => '2026-01-31']]" />
        <x-ui::date-picker :presets="['Today' => \Illuminate\Support\Carbon::parse('2026-10-27')]" />
        BLADE);

    expect($html)
        ->toContain('<div data-slot="date-picker-presets">')
        ->toContain('data-variant="outline" data-size="sm" data-date-picker-preset="{&quot;from&quot;:&quot;2026-10-21&quot;,&quot;to&quot;:&quot;2026-10-27&quot;}">Last 7 days</button>')
        ->toContain('data-date-picker-preset="{&quot;from&quot;:&quot;2026-01-01&quot;,&quot;to&quot;:&quot;2026-01-31&quot;}">Fixed</button>')
        ->toContain('data-date-picker-preset="&quot;2026-10-27&quot;">Today</button>');
});

it('renders a date and time picker, the calendar taking the date and a time input the time', function () {
    $html = (string) $this->blade('<x-ui::date-picker time default-value="2026-01-05 14:30" />');

    expect($html)
        ->toContain('<eso-date-picker data-slot="date-picker" data-mode="single" data-time="data-time" data-default-time="00:00" data-locale="en">')
        ->toContain('<eso-calendar data-slot="calendar" data-mode="single" data-value="&quot;2026-01-05&quot;"')
        ->toContain('<div data-slot="date-picker-time" wire:ignore>')
        // Its script gives the inputs ids and points the labels at them.
        ->toContain('<label data-slot="field-label">Time</label>')
        ->toMatch('/<input type="time"[^>]*value="14:30" data-date-picker-time="from">/')
        ->not->toContain('data-date-picker-time="to"');
});

it('times both ends of a range, from the default time while empty', function () {
    app()->setLocale('zh_CN');

    $html = (string) $this->blade('<x-ui::date-picker mode="range" time step="1" default-time="09:00" :default-value="[\'from\' => \'2026-01-05 08:00:00\', \'to\' => null]" />');

    expect($html)
        ->toContain('data-time="data-time" data-step="1" data-default-time="09:00"')
        ->toContain('data-value="{&quot;from&quot;:&quot;2026-01-05&quot;,&quot;to&quot;:null}"')
        ->toContain('>开始时间</label>')
        ->toContain('>结束时间</label>')
        ->toMatch('/step="1" value="08:00:00" data-date-picker-time="from">/')
        ->toMatch('/step="1" value="09:00" data-date-picker-time="to">/');
});

it('renders a time picker as a time input', function () {
    $this->blade('<x-ui::time-picker wire:model="time" step="1" />')
        ->assertSee('<input type="time" data-slot="input" data-time-picker="data-time-picker" wire:model="time" step="1">', false);
});

it('validates a date picker inside a field', function () {
    $this->withViewErrors(['date' => 'Pick a date.'])
        ->blade('<x-ui::field><x-ui::date-picker id="date" wire:model="date" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="date-error"', false)
        ->assertSeeInOrder(['</eso-date-picker>', 'Pick a date.'], false);
});
