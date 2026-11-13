<?php

use Esoboy\Ui\Livewire\Calendar;
use Esoboy\Ui\Livewire\Cascader;
use Esoboy\Ui\Livewire\Combobox;
use Esoboy\Ui\Livewire\DatePicker;
use Esoboy\Ui\Livewire\Questionnaire;
use Esoboy\Ui\Livewire\TagsInput;
use Esoboy\Ui\Livewire\Transfer;
use Esoboy\Ui\Livewire\Tree;
use Esoboy\Ui\Support\Model;
use Esoboy\Ui\Tests\Fixtures\Pickers;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Livewire;

function surveyItems(): array
{
    return [
        ['name' => 'direction', 'title' => 'Direction', 'required' => true, 'input' => true, 'other' => 'other', 'options' => ['a' => 'A', 'b' => 'B']],
        ['name' => 'sources', 'title' => 'Sources', 'multiple' => true, 'options' => ['files' => 'Files', 'tests' => 'Tests']],
        ['name' => 'depth', 'title' => 'Depth', 'options' => [['value' => 'full', 'label' => 'Full'], ['value' => 'blocked', 'label' => 'Blocked', 'disabled' => true]]],
    ];
}

it('validates required questions on the server and cannot skip them', function () {
    Livewire::test(Questionnaire::class, ['items' => surveyItems()])
        ->call('next')->assertSet('current', 'direction')->assertSee('Choose an answer to continue.')
        ->call('skip')->assertSet('current', 'direction')
        ->call('choose', 'direction', 'invalid')->assertSet('value.direction', null)
        ->call('choose', 'direction', 'a')->call('next')->assertSet('current', 'sources');
});

it('accepts empty parent answers and computes multiple selections in PHP', function () {
    Livewire::test(Questionnaire::class, ['items' => surveyItems()])
        ->set('value', [])
        ->call('choose', 'direction', 'b')->call('next')
        ->call('choose', 'sources', 'files')->call('choose', 'sources', 'tests')
        ->assertSet('value.sources', ['files', 'tests'])
        ->call('choose', 'sources', 'files')->assertSet('value.sources', ['tests'])
        ->call('next')->assertSet('current', 'depth')
        ->call('choose', 'depth', 'blocked')->assertSet('value.depth', null)
        ->call('skip')->assertDispatched('finish', fn ($event, $params) => $params['value'] === ['direction' => 'b', 'other' => '', 'sources' => ['tests'], 'depth' => null]);
});

it('requires an explicit skip for optional unanswered questions', function () {
    Livewire::test(Questionnaire::class, ['items' => surveyItems()])
        ->call('choose', 'direction', 'a')->call('next')->call('next')
        ->assertSet('current', 'sources')->assertNotDispatched('finish')
        ->call('skip')->assertSet('current', 'depth')
        ->call('submit')->assertNotDispatched('finish')
        ->call('skip')->assertDispatched('finish');
});

it('clears an alternate text answer when a single choice is selected and vice versa', function () {
    Livewire::test(Questionnaire::class, ['items' => surveyItems()])
        ->set('value.other', 'A different direction')->call('choose', 'direction', 'a')
        ->assertSet('value.other', '')->assertSet('value.direction', 'a')
        ->set('value.other', 'Another')->assertSet('value.direction', null)
        ->call('next')->assertSet('current', 'sources');
});

it('revalidates every question when submit is called directly', function () {
    Livewire::test(Questionnaire::class, ['items' => surveyItems()])
        ->set('current', 'depth')->call('submit')
        ->assertSet('current', 'direction')->assertNotDispatched('finish');
});

it('filters combobox options in PHP and rejects forged or disabled choices', function () {
    Livewire::test(Combobox::class, [
        'options' => [['value' => 'a', 'label' => 'Apple'], ['value' => 'b', 'label' => 'Banana', 'disabled' => true]],
    ])->set('query', 'app')->assertSee('Apple')->assertDontSee('Banana')
        ->call('choose', 'b')->assertSet('value', null)
        ->call('choose', 'unknown')->assertSet('value', null)
        ->call('choose', 'a')->assertSet('value', 'a')->assertSet('query', 'Apple')
        ->call('clear')->assertSet('value', null)->assertSet('query', '');
});

it('keeps autocomplete text as the formal value and accepts server suggestions', function () {
    Livewire::test(Combobox::class, ['options' => ['ada@example.com'], 'settings' => ['autocomplete' => true]])
        ->set('query', 'ada@')->assertSet('value', 'ada@')
        ->call('choose', 'ada@example.com')->assertSet('value', 'ada@example.com')->assertSet('query', 'ada@example.com');
});

it('toggles and removes multiple combobox choices in PHP', function () {
    Livewire::test(Combobox::class, ['options' => ['a' => 'A', 'b' => 'B'], 'settings' => ['multiple' => true]])
        ->call('choose', 'a')->call('choose', 'b')->assertSet('value', ['a', 'b'])
        ->call('choose', 'a')->assertSet('value', ['b'])
        ->call('remove', 'b')->assertSet('value', [])
        ->call('choose', 'b')->call('clear')->assertSet('value', []);
});

it('does not mutate disabled server controls even through direct action calls', function (string $component, array $params, array $actions) {
    $control = Livewire::test($component, [...$params, 'settings' => [...($params['settings'] ?? []), 'disabled' => true]]);
    $before = $control->get('value');
    foreach ($actions as $action) {
        $control->call(...$action);
    }
    $control->assertSet('value', $before);
})->with([
    'calendar' => [Calendar::class, ['value' => '2026-11-01'], [['select', '2026-11-02']]],
    'combobox' => [Combobox::class, ['options' => ['a' => 'A']], [['choose', 'a'], ['clear']]],
    'tags' => [TagsInput::class, ['value' => ['a']], [['remove', 'a'], ['removeLast']]],
    'transfer' => [Transfer::class, ['options' => ['a' => 'A']], [['toggleAll', 'source'], ['move', 'target']]],
    'tree' => [Tree::class, ['items' => [['value' => 'a', 'label' => 'A']]], [['choose', 'a'], ['expand', 'a']]],
    'cascader' => [Cascader::class, ['options' => ['a' => 'A']], [['choose', 0, 'a']]],
]);

it('preserves existing tree branches when selecting another parent', function () {
    $items = [
        ['value' => 'orders', 'label' => 'Orders', 'children' => [['value' => 'view', 'label' => 'View'], ['value' => 'edit', 'label' => 'Edit']]],
        ['value' => 'users', 'label' => 'Users', 'children' => [['value' => 'read', 'label' => 'Read']]],
    ];
    Livewire::test(Tree::class, ['items' => $items, 'settings' => ['checkable' => true]])
        // A modelable parent may send a checked parent without listing its leaves.
        ->set('value', ['view', 'users'])->call('choose', 'orders')
        ->assertSet('value', ['orders', 'view', 'edit', 'users', 'read'])
        ->call('choose', 'view')->assertSet('value', ['edit', 'users', 'read']);
});

it('searches transfer lists independently and checks only enabled matches', function () {
    Livewire::test(Transfer::class, [
        'options' => [['value' => 'a', 'label' => 'Apple'], ['value' => 'b', 'label' => 'Apricot', 'disabled' => true], ['value' => 'c', 'label' => 'Cherry']],
        'value' => ['c'],
    ])->set('query.source', 'ap')->call('toggleAll', 'source')->assertSet('checked', ['a'])
        ->call('move', 'target')->assertSet('value', ['a', 'c'])->assertSet('checked', [])
        ->set('checked', ['c'])->call('move', 'source')->assertSet('value', ['a']);
});

it('enforces calendar navigation bounds and validates preset dates and time values', function () {
    Livewire::test(DatePicker::class, [
        'settings' => ['mode' => 'single', 'min' => '2026-11-01', 'max' => '2026-11-30', 'time' => true, 'presets' => ['invalid' => '2026-02-31']],
        'value' => '2026-11-05 09:30',
    ])->call('previous')->assertSet('month', '2026-11')
        ->call('next')->assertSet('month', '2026-11')
        ->call('goTo', 'not-a-month')->assertSet('month', '2026-11')
        ->call('preset', 'invalid')->assertSet('value', '2026-11-05 09:30')
        ->call('pickTime', 'from', 'hour', 29)->assertSet('value', '2026-11-05 09:30')
        ->call('pickTime', 'from', 'hour', 10)
        ->call('pickTime', 'from', 'minute', 45)->assertSet('value', '2026-11-05 10:45');
});

it('recognizes native Livewire blur modifiers in interaction bindings', function () {
    Livewire::test(Pickers::class);
    // No current component outside rendering: the model key still remains available.
    $attributes = new ComponentAttributeBag(['wire:model.blur' => 'form.color']);
    expect(Model::binding($attributes))->toMatchArray(['live' => true, 'blur' => true])
        ->and(Model::key($attributes))->toBe('form.color');
});

it('recomputes server-derived fields when a bound model is replaced', function () {
    Livewire::test(Combobox::class, ['options' => ['a' => 'Apple', 'b' => 'Banana']])
        ->set('value', 'b')->assertSet('query', 'Banana')
        ->set('value', null)->assertSet('query', '');

    Livewire::test(Cascader::class, ['options' => [['value' => 'a', 'label' => 'A', 'children' => [['value' => 'b', 'label' => 'B']]]]])
        ->set('value', ['a', 'b'])->assertSet('path', ['a', 'b']);

    Livewire::test(Calendar::class, ['settings' => ['time' => true]])
        ->set('value', '2026-12-12 15:45')->assertSet('month', '2026-12')->assertSet('times.from', '15:45');
});

it('keeps a cascader browsing path across requests until a leaf is chosen', function () {
    Livewire::test(Cascader::class, ['options' => [['value' => 'a', 'label' => 'A', 'children' => [['value' => 'b', 'label' => 'B']]]]])
        ->call('choose', 0, 'a')->assertSet('value', [])->assertSet('path', ['a'])
        ->call('choose', 1, 'b')->assertSet('value', ['a', 'b']);
});
