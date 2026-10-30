<?php

use Esoboy\Ui\Support\Options;

$items = [
    ['value' => 'orders', 'label' => 'Orders', 'children' => [
        ['value' => 'orders.view', 'label' => 'View'],
        ['value' => 'orders.export', 'label' => 'Export', 'disabled' => true],
    ]],
    ['value' => 'settings', 'label' => 'Settings'],
];

it('renders items as an ARIA tree, open where asked', function () use ($items) {
    $html = (string) $this->blade('<x-ui::tree :items="$items" checkable aria-label="Permissions" :default-expanded="[\'orders\']" :default-value="[\'orders.view\']" />', ['items' => $items]);

    expect($html)
        ->toContain('<eso-tree data-slot="tree" data-checkable="data-checkable" data-value="[&quot;orders.view&quot;]">')
        ->toContain('<ul role="tree" data-slot="tree-list" aria-multiselectable="true" aria-label="Permissions">')
        ->toContain('<li role="treeitem" tabindex="-1" wire:key="tree-item-orders" data-slot="tree-item" data-value="orders" aria-expanded="true">')
        ->toContain('<span data-slot="tree-item-label">Orders</span>')
        ->toContain('<input type="checkbox" data-slot="checkbox" data-tree-check tabindex="-1" aria-hidden="true" >')
        ->toContain('<ul role="group" data-slot="tree-group" >')
        ->toContain('data-value="orders.export" aria-disabled="true"')
        ->toContain('<li role="treeitem" tabindex="-1" wire:key="tree-item-settings" data-slot="tree-item" data-value="settings">')
        ->not->toContain('data-tree-search');
});

it('composes items with icons, collapsed by default', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::tree default-value="a.txt">
            <x-ui::tree.item value="docs" label="docs">
                <x-slot:icon>F</x-slot:icon>
                <x-ui::tree.item value="a.txt" label="a.txt" />
            </x-ui::tree.item>
        </x-ui::tree>
        BLADE);

    expect($html)
        ->toContain('data-value="&quot;a.txt&quot;"')
        ->toContain('data-value="docs" aria-expanded="false"')
        ->toContain('<span data-slot="tree-item-icon">F</span>')
        ->toContain('<ul role="group" data-slot="tree-group"  hidden >')
        ->not->toContain('data-tree-check');
});

it('puts a search box above a searchable tree, with its empty text', function () {
    app()->setLocale('zh_CN');

    $html = (string) $this->blade('<x-ui::tree searchable :items="[[\'value\' => \'a\', \'label\' => \'A\']]" />');

    expect($html)
        ->toContain('<div data-slot="tree-search">')
        ->toContain('<input type="text" data-slot="tree-search-input" data-tree-search placeholder="搜索" aria-label="搜索" autocomplete="off" spellcheck="false">')
        ->toContain('<div data-slot="tree-empty" hidden>没有结果。</div>');

    expect((string) $this->blade('<x-ui::tree searchable placeholder="Find a file" :items="[]" />'))->toContain('placeholder="Find a file"');
});

it('renders lazy items as closed parents with a spinner, and names the load method', function () {
    $html = (string) $this->blade('<x-ui::tree load="loadChildren" draggable :items="[[\'value\' => \'eng\', \'label\' => \'Engineering\', \'lazy\' => true], [\'value\' => \'ops\', \'label\' => \'Ops\', \'lazy\' => true, \'children\' => [[\'value\' => \'ops.a\', \'label\' => \'A\']]]]" />');

    expect($html)
        ->toContain('<eso-tree data-slot="tree" data-value="null" data-load="loadChildren" data-draggable="data-draggable">')
        ->toContain('data-value="eng" data-lazy="data-lazy" aria-expanded="false">')
        ->toContain('data-slot="tree-item-spinner"')
        ->toMatch('/data-value="eng" data-lazy="data-lazy"[\s\S]*?<ul role="group" data-slot="tree-group"  hidden >\s*<\/ul>/')
        ->toContain('data-value="ops" aria-expanded="false">');
});

it('renders a tree select with the chosen labels', function () use ($items) {
    $html = (string) $this->blade('<x-ui::tree-select :items="$items" checkable searchable load="more" :default-value="[\'orders.view\', \'settings\']" placeholder="Pick" class="w-64" />', ['items' => $items]);

    expect($html)
        ->toContain('<eso-tree-select data-slot="tree-select" data-placeholder="Pick" data-checkable="data-checkable">')
        ->toContain('<button class="w-64" type="button" data-slot="select-trigger" data-tree-select-trigger')
        ->toContain('<span data-slot="select-value">View, Settings</span>')
        ->toContain('popover="auto" data-slot="tree-select-content"')
        ->toContain('<eso-tree data-slot="tree" data-checkable="data-checkable"')
        ->toContain('data-load="more"')
        ->toContain('data-tree-search');

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
