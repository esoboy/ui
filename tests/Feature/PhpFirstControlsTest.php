<?php

use Esoboy\Ui\Livewire\Calendar;
use Esoboy\Ui\Livewire\Cascader;
use Esoboy\Ui\Livewire\DatePicker;
use Esoboy\Ui\Livewire\TagsInput;
use Esoboy\Ui\Livewire\Transfer;
use Esoboy\Ui\Livewire\Tree;
use Esoboy\Ui\Support\SidebarState;
use Illuminate\Http\Request;
use Livewire\Livewire;

it('renders supplied OTP characters in custom Blade slots on the first response', function () {
    $this->blade(<<<'BLADE'
        <x-ui::input-otp value="07" name="otp">
            <x-ui::input-otp.slot :index="0" />
            <x-ui::input-otp.slot :index="1" />
        </x-ui::input-otp>
        BLADE)
        ->assertSeeMarkup('<div data-slot="input-otp-slot" data-index="0">0</div>')
        ->assertSeeMarkup('<div data-slot="input-otp-slot" data-index="1">7</div>');
});

it('renders the combobox empty-state and supplementary Blade slots', function () {
    $this->blade(<<<'BLADE'
        <x-ui::combobox :options="[]">
            <x-slot:empty-state><strong>No eligible users</strong></x-slot:empty-state>
            <button type="button" wire:click="requestAccess">Request access</button>
        </x-ui::combobox>
        BLADE)
        ->assertSeeMarkup('<strong>No eligible users</strong>')
        ->assertSeeMarkup('<button wire:click="requestAccess">Request access</button>');
});

it('renders supplementary Blade content in the internal Livewire control', function (string $component) {
    $this->blade("<x-ui::{$component}><em>Caller content</em></x-ui::{$component}>")
        ->assertSeeMarkup('<em>Caller content</em>');
})->with(['calendar', 'date-picker', 'time-picker', 'cascader', 'tree', 'tree-select', 'transfer', 'tags-input', 'emoji-picker', 'questionnaire']);

it('renders a simple select and its selected label without client initialization', function () {
    $html = (string) $this->blade('<x-ui::select name="status" value="published" :options="[\'draft\' => \'Draft\', \'published\' => \'Published\']" placeholder="Choose" />');

    expect($html)
        ->toContain('data-slot="select-value" data-placeholder="Choose">Published</span>')
        ->toContain('name="status"')
        ->toContain('value="published" selected')
        ->toContain('x-data="uiSelect"');
});

it('renders custom select parts with the selected rich label on the server', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::select value="pro">
            <x-ui::select.trigger><x-ui::select.value placeholder="Choose" /></x-ui::select.trigger>
            <x-ui::select.content>
                <x-ui::select.item value="pro"><strong>Pro</strong></x-ui::select.item>
            </x-ui::select.content>
        </x-ui::select>
        BLADE);

    expect($html)->toContain('data-placeholder="Choose"><strong>Pro</strong></span>');
});

it('keeps independent sidebar preferences in their own scope', function () {
    $cookie = SidebarState::cookie('admin');
    app()->instance('request', Request::create('/', cookies: [$cookie => 'false']));

    expect(SidebarState::open(true, 'admin'))->toBeFalse()
        ->and(SidebarState::open())->toBeTrue();
});

it('renders calendar dates in PHP and selects through a Livewire action', function () {
    Livewire::test(Calendar::class, ['value' => '2026-10-26'])
        ->assertSeeHtml('data-day="2026-10-26"')
        ->assertDontSeeHtml('<template')
        ->call('select', '2026-10-27')
        ->assertSet('value', '2026-10-27');
});

it('applies date constraints and range ordering on the server', function () {
    Livewire::test(Calendar::class, [
        'settings' => ['mode' => 'range', 'min' => '2026-10-01', 'max' => '2026-10-31', 'disabledDates' => ['2026-10-12']],
        'value' => ['from' => null, 'to' => null],
    ])
        ->call('select', '2026-09-30')->assertSet('value.from', null)
        ->call('select', '2026-10-12')->assertSet('value.from', null)
        ->call('select', '2026-02-31')->assertSet('value.from', null)
        ->call('select', '2026-10-20')->assertSet('value.from', '2026-10-20')
        ->call('select', '2026-10-10')->assertSet('value', ['from' => '2026-10-10', 'to' => '2026-10-20']);
});

it('renders a picker label before JavaScript and joins dates and times on the server', function () {
    Livewire::test(DatePicker::class, [
        'settings' => ['mode' => 'single', 'time' => true, 'step' => null, 'presets' => [], 'placeholder' => 'Choose'],
        'value' => '2026-10-29 10:30',
    ])->assertSee('October 29, 2026')->assertSee('10:30 AM')
        ->call('pickTime', 'from', 'hour', 14)
        ->call('pickTime', 'from', 'minute', 45)->assertSet('value', '2026-10-29 14:45')
        ->call('select', '2026-10-30')->assertSet('value', '2026-10-30 14:45');
});

it('checks tree descendants and settles ancestors in PHP', function () {
    Livewire::test(Tree::class, [
        'items' => [['value' => 'team', 'label' => 'Team', 'children' => [
            ['value' => 'a', 'label' => 'Alice'], ['value' => 'b', 'label' => 'Bob'],
        ]]], 'settings' => ['checkable' => true],
    ])->call('choose', 'team')->assertSet('value', ['team', 'a', 'b'])
        ->call('choose', 'a')->assertSet('value', ['b']);
});

it('moves enabled checked transfer options and keeps option order', function () {
    Livewire::test(Transfer::class, [
        'options' => [['value' => 'a', 'label' => 'Alice'], ['value' => 'b', 'label' => 'Bob', 'disabled' => true], ['value' => 'c', 'label' => 'Carol']],
        'value' => ['c'],
    ])->set('checked', ['b', 'a'])->call('move', 'target')->assertSet('value', ['a', 'c'])
        ->set('query.target', 'Alice')->call('toggleAll', 'target')->assertSet('checked', ['b', 'a']);
});

it('normalizes tags and enforces limits on the server', function () {
    Livewire::test(TagsInput::class, ['settings' => ['max' => 3]])
        ->set('entry', " a, a，b\nc,d ")->call('add')->assertSet('value', ['a', 'b', 'c'])
        ->call('remove', 'b')->assertSet('value', ['a', 'c'])
        ->call('removeLast')->assertSet('value', ['a']);
});

it('validates cascader paths in PHP and never selects disabled ancestors', function () {
    Livewire::test(Cascader::class, [
        'options' => [['value' => 'a', 'label' => 'A', 'children' => [['value' => 'b', 'label' => 'B']]], ['value' => 'x', 'label' => 'X', 'disabled' => true]],
    ])->call('choose', 1, 'b')->assertSet('value', [])
        ->call('choose', 0, 'x')->assertSet('value', [])
        ->call('choose', 0, 'a')->assertSet('path', ['a'])->assertSet('value', [])
        ->call('choose', 1, 'b')->assertSet('value', ['a', 'b']);
});
