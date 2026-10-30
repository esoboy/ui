<?php

use Esoboy\Ui\Support\Options;

$options = [
    ['value' => 'zj', 'label' => 'Zhejiang', 'children' => [
        ['value' => 'hz', 'label' => 'Hangzhou', 'children' => [['value' => 1, 'label' => 'Xihu']]],
    ]],
    ['value' => 'bj', 'label' => 'Beijing', 'disabled' => true],
];

it('finds the labels along a path', function () use ($options) {
    expect(Options::labels($options, ['zj', 'hz', '1']))->toBe(['Zhejiang', 'Hangzhou', 'Xihu'])
        ->and(Options::labels($options, ['zj', 'nope']))->toBe([])
        ->and(Options::labels($options, []))->toBe([]);
});

it('keeps only what the browser needs, with string values', function () use ($options) {
    expect(Options::normalize($options))->toBe([
        ['value' => 'zj', 'label' => 'Zhejiang', 'children' => [
            ['value' => 'hz', 'label' => 'Hangzhou', 'children' => [['value' => '1', 'label' => 'Xihu']]],
        ]],
        ['value' => 'bj', 'label' => 'Beijing', 'disabled' => true],
    ]);
});

it('takes options as value => label pairs too, children included', function () {
    expect(Options::normalize(['a' => 'Alpha', ['value' => 'b', 'label' => 'Beta', 'children' => [7 => 'Seven']]]))->toBe([
        ['value' => 'a', 'label' => 'Alpha'],
        ['value' => 'b', 'label' => 'Beta', 'children' => [['value' => '7', 'label' => 'Seven']]],
    ]);
});

it('renders the trigger with the chosen path', function () use ($options) {
    $html = (string) $this->blade('<x-ui::cascader :options="$options" :default-value="[\'zj\', \'hz\']" name="region" placeholder="Pick" />', ['options' => $options]);

    expect($html)
        ->toContain('data-slot="cascader" data-value="[&quot;zj&quot;,&quot;hz&quot;]" data-separator="/" data-placeholder="Pick" data-name="region"')
        ->toContain('<script type="application/json" data-cascader-options>[{"value":"zj"')
        ->toContain('<span data-slot="select-value">Zhejiang / Hangzhou</span>')
        ->toContain('popover="auto" data-slot="cascader-content"')
        ->toContain('<input type="hidden" name="region[]" value="zj">')
        ->not->toContain('data-placeholder >');
});

it('shows the placeholder for an empty or unknown path', function () use ($options) {
    $this->blade('<x-ui::cascader :options="$options" :default-value="[\'nope\']" placeholder="Pick" />', ['options' => $options])
        ->assertSee('data-value="[]"', false)
        ->assertSee('<span data-slot="select-value">Pick</span>', false)
        ->assertSee('data-placeholder', false);
});
