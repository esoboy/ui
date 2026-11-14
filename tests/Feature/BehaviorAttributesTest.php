<?php

use Esoboy\Ui\Support\HtmlFragment;

it('forwards dynamic behavior attributes without changing their decoded values', function (string $opening, string $closing) {
    $html = (string) $this->blade($opening.' '.<<<'BLADE'
        :aria-label="$label" :wire:click="$action" :class="$classes" :data-note="$note">
            <button type="button" title="Keep &amp;quot; literally" class="inner">Open</button>
        BLADE
        .$closing, [
            'label' => 'Edit & review "details"',
            'action' => "save('a&b')",
            'classes' => '[&>svg]:size-4',
            'note' => 'Literal &quot; and <tag>',
        ]);

    $control = HtmlFragment::parse($html)->querySelector('button');

    expect($control->getAttribute('aria-label'))->toBe('Edit & review "details"')
        ->and($control->getAttribute('wire:click'))->toBe("save('a&b')")
        ->and($control->getAttribute('class'))->toBe('[&>svg]:size-4 inner')
        ->and($control->getAttribute('data-note'))->toBe('Literal &quot; and <tag>')
        ->and($control->getAttribute('title'))->toBe('Keep &quot; literally');
})->with([
    'dialog trigger' => ['<x-ui::dialog name="editing"><x-ui::dialog.trigger', '</x-ui::dialog.trigger></x-ui::dialog>'],
    'dialog close' => ['<x-ui::dialog name="editing"><x-ui::dialog.close', '</x-ui::dialog.close></x-ui::dialog>'],
    'popover trigger' => ['<x-ui::popover><x-ui::popover.trigger', '</x-ui::popover.trigger></x-ui::popover>'],
]);
