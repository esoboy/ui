<?php

use Esoboy\Ui\Support\HtmlFragment;

it('excludes disabled controls from submitted values and preserves enabled names', function (string $component, string $props, array $expected) {
    foreach ([false, true] as $disabled) {
        $html = (string) $this->blade('<x-ui::'.$component.' name="choice" :disabled="$disabled" '.$props.' />', compact('disabled'));
        $fields = HtmlFragment::parse($html)->querySelectorAll('input[type="hidden"][name]:not([disabled])');
        $values = [];
        foreach ($fields as $field) {
            $values[] = [$field->getAttribute('name'), $field->getAttribute('value')];
        }
        expect($values)->toBe($disabled ? [] : $expected);
    }
})->with([
    'combobox' => ['combobox', 'value="a" :options="[\'a\' => \'A\']"', [['choice', 'a']]],
    'multiple combobox' => ['combobox', 'multiple :value="[\'a\']" :options="[\'a\' => \'A\']"', [['choice[]', 'a']]],
    'tags' => ['tags-input', ':value="[\'a\', \'b\']"', [['choice[]', 'a'], ['choice[]', 'b']]],
    'rating' => ['rating', ':value="3"', [['choice', '3']]],
    'tree' => ['tree', 'value="a" :items="[[\'value\' => \'a\', \'label\' => \'A\']]"', [['choice', 'a']]],
    'tree select' => ['tree-select', 'checkable :value="[\'a\']" :items="[[\'value\' => \'a\', \'label\' => \'A\']]"', [['choice[]', 'a']]],
    'cascader' => ['cascader', ':value="[\'a\']" :options="[[\'value\' => \'a\', \'label\' => \'A\']]"', [['choice[]', 'a']]],
    'transfer' => ['transfer', ':value="[\'a\']" :items="[[\'value\' => \'a\', \'label\' => \'A\']]"', [['choice[]', 'a']]],
    'calendar range' => ['calendar', 'mode="range" :value="[\'from\' => \'2026-11-01\', \'to\' => \'2026-11-02\']"', [['choice[from]', '2026-11-01'], ['choice[to]', '2026-11-02']]],
    'date picker' => ['date-picker', 'value="2026-11-01"', [['choice', '2026-11-01']]],
    'time picker' => ['time-picker', 'value="10:00"', [['choice', '10:00']]],
    'color picker' => ['color-picker', 'value="#112233"', [['choice', '#112233']]],
]);
