<?php

use Dom\HTMLDocument;

it('renders caller label attributes on the control', function (string $blade, string $control) {
    $html = (string) $this->blade(str_replace(
        'LABEL_ATTRIBUTES',
        'id="field-control" aria-label="Field name" aria-labelledby="field-label"',
        $blade,
    ));

    expect($html)->toContainMarkup("<{$control} id=\"field-control\" aria-label=\"Field name\" aria-labelledby=\"field-label\">")
        ->and(substr_count($html, ' id="field-control"'))->toBe(1)
        ->and(substr_count($html, ' aria-label="Field name"'))->toBe(1)
        ->and(substr_count($html, ' aria-labelledby="field-label"'))->toBe(1);
})->with([
    'select options' => ['<x-ui::select LABEL_ATTRIBUTES :options="[\'one\' => \'One\']" />', 'button data-slot="select-trigger"'],
    'select parts' => ['<x-ui::select LABEL_ATTRIBUTES><x-ui::select.trigger>Choose</x-ui::select.trigger><x-ui::select.content><x-ui::select.item value="one">One</x-ui::select.item></x-ui::select.content></x-ui::select>', 'button data-slot="select-trigger"'],
    'date picker' => ['<x-ui::date-picker LABEL_ATTRIBUTES />', 'button data-date-picker-trigger'],
    'color picker' => ['<x-ui::color-picker LABEL_ATTRIBUTES />', 'button data-color-picker-trigger'],
    'cascader' => ['<x-ui::cascader LABEL_ATTRIBUTES />', 'button data-cascader-trigger'],
    'tree select' => ['<x-ui::tree-select LABEL_ATTRIBUTES />', 'button data-tree-select-trigger'],
    'tags input' => ['<x-ui::tags-input LABEL_ATTRIBUTES />', 'input data-slot="tags-input-input"'],
    'single slider' => ['<x-ui::slider LABEL_ATTRIBUTES />', 'input type="range"'],
]);

it('keeps the text of a bound label on a custom select trigger', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::select :aria-label="$label">
            <x-ui::select.trigger>Choose</x-ui::select.trigger>
        </x-ui::select>
        BLADE, ['label' => 'Field "name" & details']);

    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

    expect($document->querySelector('[data-slot="select-trigger"]')?->getAttribute('aria-label'))
        ->toBe('Field "name" & details');
});

it('names the semantic list and preserves the widget ID', function (string $component, string $list) {
    $html = (string) $this->blade("<x-ui::{$component} id=\"choices\" aria-label=\"Choose an item\" aria-labelledby=\"choices-label\" />");

    expect($html)->toContainMarkup("<eso-{$component} id=\"choices\">")
        ->toContainMarkup("<{$list} aria-label=\"Choose an item\" aria-labelledby=\"choices-label\">")
        ->and(substr_count($html, ' aria-label="Choose an item"'))->toBe(1)
        ->and(substr_count($html, ' aria-labelledby="choices-label"'))->toBe(1);
})->with([
    'tree' => ['tree', 'ul role="tree"'],
    'emoji picker' => ['emoji-picker', 'div role="listbox"'],
]);
