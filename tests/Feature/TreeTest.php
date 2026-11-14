<?php

use Esoboy\Ui\Livewire\Tree;
use Esoboy\Ui\Support\HtmlFragment;
use Esoboy\Ui\Support\Options;
use Livewire\Livewire;

// Instrument traversal cost while exercising the public Blade entry, not a timing threshold.
class TreeTraversalBudget extends Tree
{
    public static int $indexedNodes = 0;

    public function index(): array
    {
        $index = parent::index();
        self::$indexedNodes += count($index);

        return $index;
    }
}

it('renders a large checkable tree with a linear traversal budget', function () {
    Livewire::component('ui.tree', TreeTraversalBudget::class);
    TreeTraversalBudget::$indexedNodes = 0;
    $items = array_map(fn ($id) => ['value' => (string) $id, 'label' => 'Node '.$id], range(1, 200));
    $html = (string) $this->blade('<x-ui::tree checkable :items="$items" />', compact('items'));

    expect(HtmlFragment::parse($html)->querySelectorAll('[role="treeitem"]')->length)->toBe(200)
        ->and(TreeTraversalBudget::$indexedNodes)->toBeLessThanOrEqual(200 * 8);
});

it('refreshes checked and matching states after each tree action', function (string $component) {
    $items = [['value' => 'root', 'label' => 'Root', 'children' => [
        ['value' => 'a', 'label' => 'Apple'],
        ['value' => 'b', 'label' => 'Banana'],
        ['value' => 'off', 'label' => 'Disabled', 'disabled' => true],
    ]]];
    $tree = Livewire::test($component, ['items' => $items, 'settings' => ['checkable' => true, 'searchable' => true]])
        ->call('choose', 'a');
    $document = HtmlFragment::parse($tree->html());
    expect($document->querySelector('[data-value="root"]')->getAttribute('aria-checked'))->toBe('mixed');

    $tree->call('choose', 'b')->assertSet('value', ['root', 'a', 'b']);
    $document = HtmlFragment::parse($tree->html());
    expect($document->querySelector('[data-value="root"]')->getAttribute('aria-checked'))->toBe('true')
        ->and($document->querySelector('[data-value="off"]')->getAttribute('aria-checked'))->toBe('false');

    $tree->set('value', [])->set('query', 'Banana');
    $document = HtmlFragment::parse($tree->html());
    expect($document->querySelector('[data-value="root"]')->getAttribute('aria-checked'))->toBe('false')
        ->and($document->querySelector('[data-value="root"]')->hasAttribute('hidden'))->toBeFalse()
        ->and($document->querySelector('[data-value="a"]')->hasAttribute('hidden'))->toBeTrue()
        ->and($document->querySelector('[data-value="b"]')->hasAttribute('hidden'))->toBeFalse();
    $tree->set('query', 'missing');
    expect(HtmlFragment::parse($tree->html())->querySelector('[data-slot="tree-empty"]')->hasAttribute('hidden'))->toBeFalse();
})->with(['ui.tree', 'ui.tree-select']);

it('includes lazy children in checked states without retaining the previous render', function () {
    app()->instance('tree-test-loader', new class
    {
        public function __invoke(string $value): array
        {
            return [['value' => $value.'.child', 'label' => 'Loaded child']];
        }
    });
    $tree = Livewire::test('ui.tree', [
        'items' => [['value' => 'root', 'label' => 'Root', 'lazy' => true]],
        'settings' => ['checkable' => true, 'load' => 'tree-test-loader'],
        'value' => ['root'],
    ])->call('expand', 'root')->assertSet('value', ['root', 'root.child']);
    expect(HtmlFragment::parse($tree->html())->querySelector('[data-value="root.child"]')->getAttribute('aria-checked'))->toBe('true');
    $tree->call('choose', 'root.child')->assertSet('value', []);
    expect(HtmlFragment::parse($tree->html())->querySelector('[data-value="root"]')->getAttribute('aria-checked'))->toBe('false');
});

$items = [
    ['value' => 'orders', 'label' => 'Orders', 'children' => [
        ['value' => 'orders.view', 'label' => 'View'],
        ['value' => 'orders.export', 'label' => 'Export', 'disabled' => true],
    ]],
    ['value' => 'settings', 'label' => 'Settings'],
];

it('renders expanded tree items and checks their enabled descendants in PHP', function () use ($items) {
    $html = (string) $this->blade('<x-ui::tree :items="$items" checkable aria-label="Permissions" :expanded="[\'orders\']" :value="[\'orders.view\']" />', compact('items'));
    expect($html)->toContain('data-checkable')->toContainMarkup('<ul role="tree" aria-multiselectable="true" aria-label="Permissions">')->toContainMarkup('<li role="treeitem" data-value="orders" aria-expanded="true">')->toContainMarkup('<li role="treeitem" data-value="orders.export" aria-disabled="true">')->not->toContain('data-tree-search');
});

it('renders icons and collapsed children from PHP items', function () {
    $html = (string) $this->blade('<x-ui::tree value="a.txt" :items="[[\'value\' => \'docs\', \'label\' => \'docs\', \'icon\' => \'folder\', \'children\' => [[\'value\' => \'a.txt\', \'label\' => \'a.txt\']]]]" />');
    expect($html)->toContainMarkup('<li role="treeitem" data-value="docs" aria-expanded="false">')->toContain('data-slot="tree-item-icon"')->toContain('data-slot="tree-group"')->not->toContain('data-tree-check');
});

it('renders tree search and its translated empty state', function () {
    app()->setLocale('zh_CN');
    $this->blade('<x-ui::tree searchable :items="[[\'value\' => \'a\', \'label\' => \'A\']]" />')->assertSee('placeholder="搜索"', false)->assertSee('wire:model.live.debounce.150ms="query"', false)->assertSee('没有结果。');
});

it('renders lazy items with an internal server loading action', function () {
    $this->blade('<x-ui::tree draggable :items="[[\'value\' => \'eng\', \'label\' => \'Engineering\', \'lazy\' => true]]" />')->assertSee('data-draggable', false)->assertSee('data-tree-item-spinner', false)->assertSee('aria-expanded="false"', false);
});

it('renders selected tree labels and preserves native form names', function () use ($items) {
    $html = (string) $this->blade('<x-ui::tree-select :items="$items" checkable searchable :value="[\'orders.view\', \'settings\']" placeholder="Pick" class="w-64" name="permissions" />', compact('items'));
    expect($html)->toContain('View, Settings')->toContain('class="w-64"')->toContain('data-tree-search')->toContainMarkup('<input type="hidden" name="permissions[]" value="settings">');
    expect(Options::find($items, ['settings', 'orders.view']))->toBe(['View', 'Settings']);
});

it('puts loaded children in place of a lazy item', function () {
    $tree = [['value' => 'eng', 'label' => 'Engineering', 'children' => [['value' => 'web', 'label' => 'Web', 'lazy' => true]]]];

    expect(Options::put($tree, 'web', [['value' => 'ada', 'label' => 'Ada']]))
        ->toBe([['value' => 'eng', 'label' => 'Engineering', 'children' => [['children' => [['value' => 'ada', 'label' => 'Ada']], 'value' => 'web', 'label' => 'Web']]]]);
});

it('moves an item before, after or inside another', function () {
    $tree = [
        ['value' => 'a', 'label' => 'A', 'children' => [['value' => 'a1', 'label' => 'A1'], ['value' => 'a2', 'label' => 'A2']]],
        ['value' => 'b', 'label' => 'B'],
    ];

    $values = function (array $items) use (&$values): array {
        return array_map(fn ($item) => isset($item['children']) ? [$item['value'] => $values($item['children'])] : $item['value'], $items);
    };

    expect($values(Options::move($tree, 'b', 'a1', 'before')))->toBe([['a' => ['b', 'a1', 'a2']]])
        ->and($values(Options::move($tree, 'a1', 'b', 'after')))->toBe([['a' => ['a2']], 'b', 'a1'])
        ->and($values(Options::move($tree, 'a2', 'b', 'inside')))->toBe([['a' => ['a1']], ['b' => ['a2']]])
        ->and(Options::move($tree, 'a', 'a2', 'inside'))->toBe($tree)
        ->and(Options::move($tree, 'a', 'missing', 'before'))->toBe($tree);
});
