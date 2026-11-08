<?php

use Esoboy\Ui\Support\Options;

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
