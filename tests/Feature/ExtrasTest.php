<?php

/*
 * Status colours, Code Block, Trace, Questionnaire.
 */

use Esoboy\Ui\Support\QuestionnaireSteps;

it('defines the status colours beside destructive, light and dark', function () {
    $css = file_get_contents(__DIR__.'/../../resources/css/ui.css');

    foreach (['success', 'warning', 'info'] as $name) {
        expect($css)->toMatch("/--color-{$name}: light-dark\\(oklch\\([^)]+\\), oklch\\([^)]+\\)\\);/")
            ->toContain("[data-variant=\"{$name}\"]");
    }
});

it('passes status variants through on Button, Badge, Alert and Bubble', function (string $variant) {
    $html = (string) $this->blade(<<<BLADE
        <x-ui::button variant="{$variant}">B</x-ui::button>
        <x-ui::badge variant="{$variant}">B</x-ui::badge>
        <x-ui::alert variant="{$variant}">A</x-ui::alert>
        <x-ui::bubble variant="{$variant}"><x-ui::bubble.content>B</x-ui::bubble.content></x-ui::bubble>
        BLADE);

    expect(substr_count($html, "data-variant=\"{$variant}\""))->toBe(4);
})->with(['success', 'warning', 'info']);

describe('code block', function () {
    it('renders code as escaped rows, numbered from the start line', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::code-block size="sm">
                <x-ui::code-block.header>
                    <x-ui::code-block.title>app.php</x-ui::code-block.title>
                    <x-ui::code-block.actions><x-ui::code-block.copy-button /></x-ui::code-block.actions>
                </x-ui::code-block.header>
                <x-ui::code-block.content language="php" line-numbers :start-line="10" :code="$code" />
                <x-ui::code-block.footer>PHP</x-ui::code-block.footer>
            </x-ui::code-block>
            BLADE, ['code' => "<?php\n\necho 'a < b';\n"]);

        $view->assertSeeInOrder([
            '<div data-slot="code-block" data-variant="default" data-size="sm">',
            '<div data-slot="code-block-header">',
            '<div data-slot="code-block-title">app.php</div>',
            'data-code-block-copy="data-code-block-copy" aria-label="Copy" data-label-copy="Copy" data-label-copied="Copied"',
            '<span aria-live="polite" class="sr-only" data-code-block-status></span>',
            '<pre data-slot="code-block-content" data-language="php" data-numbered="data-numbered" dir="ltr" tabindex="0" style="counter-reset: line 9;">',
            '<code data-slot="code-block-code"><span data-slot="code-block-line" data-variant="default"><span data-slot="code-block-line-content">&lt;?php</span></span>'
                ."<span data-slot=\"code-block-line\" data-variant=\"default\"><span data-slot=\"code-block-line-content\">\n</span></span>"
                .'<span data-slot="code-block-line" data-variant="default"><span data-slot="code-block-line-content">echo &#039;a &lt; b&#039;;</span></span></code></pre>',
            '<div data-slot="code-block-footer">PHP</div>',
        ], false);
    });

    it('takes hand-written rows for diffs', function () {
        $this->blade('<x-ui::code-block.content wrap><x-ui::code-block.line variant="removed">a</x-ui::code-block.line><x-ui::code-block.line variant="added" /></x-ui::code-block.content>')
            ->assertSee('<pre data-slot="code-block-content" data-wrap="data-wrap" dir="ltr" tabindex="0" style="counter-reset: line 0;"><code data-slot="code-block-code"><span data-slot="code-block-line" data-variant="removed"><span data-slot="code-block-line-content">a</span></span>', false)
            ->assertSee("<span data-slot=\"code-block-line\" data-variant=\"added\"><span data-slot=\"code-block-line-content\">\n</span></span></code></pre>", false);
    });

    it('copies a given value', function () {
        $this->blade('<x-ui::code-block.copy-button value="npm i shiki" />')->assertSee('data-value="npm i shiki"', false);
    });
});

describe('trace', function () {
    it('renders lines with their status', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::trace>
                <x-ui::trace.line status="running">
                    <x-ui::trace.icon>i</x-ui::trace.icon>
                    <x-ui::trace.title>Searching</x-ui::trace.title>
                    <x-ui::trace.description>0.4s</x-ui::trace.description>
                </x-ui::trace.line>
            </x-ui::trace>
            BLADE);

        $view->assertSeeInOrder([
            '<div data-slot="trace">',
            '<span data-slot="trace-line" data-status="running">',
            '<span data-slot="trace-icon" aria-hidden="true">i</span>',
            '<span data-slot="trace-title">Searching</span>',
            '<span data-slot="trace-description">0.4s</span>',
        ], false);
    });

    it('collapses steps in a Collapsible, open by default when asked', function () {
        $template = '<x-ui::trace.collapsible :default-open="$open"><x-ui::trace.collapsible-trigger>Read</x-ui::trace.collapsible-trigger><x-ui::trace.collapsible-content>files</x-ui::trace.collapsible-content></x-ui::trace.collapsible>';

        $this->blade($template, ['open' => true])
            ->assertSee('<eso-collapsible data-slot="trace-collapsible" data-open="data-open">', false)
            ->assertSee('<button type="button" data-slot="trace-collapsible-trigger" data-collapsible-trigger="data-collapsible-trigger" aria-expanded="true">', false)
            ->assertSee('<div data-slot="trace-collapsible-content" data-collapsible-content >', false)
            ->assertSee('<div data-slot="trace-collapsible-content-inner">files</div>', false);

        $this->blade($template, ['open' => false])
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('data-collapsible-content  hidden >', false);
    });
});

describe('questionnaire', function () {
    it('renders the opening item, progress and actions from items', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::questionnaire :items="[['name' => 'direction', 'required' => true], 'detail']" shortcuts="letters" wire:submit="save">
                <x-ui::questionnaire.progress />
                <x-ui::questionnaire.item name="direction" required>
                    <x-ui::questionnaire.title>What next?</x-ui::questionnaire.title>
                    <x-ui::questionnaire.description>Choose one.</x-ui::questionnaire.description>
                    <x-ui::questionnaire.choices wire:model="direction">
                        <x-ui::questionnaire.choice value="a">A<x-ui::questionnaire.choice-description>About A</x-ui::questionnaire.choice-description></x-ui::questionnaire.choice>
                        <x-ui::questionnaire.input aria-label="Other" />
                    </x-ui::questionnaire.choices>
                    <x-ui::questionnaire.error />
                </x-ui::questionnaire.item>
                <x-ui::questionnaire.item name="detail" multiple>
                    <x-ui::questionnaire.choices><x-ui::questionnaire.choice value="x">X</x-ui::questionnaire.choice></x-ui::questionnaire.choices>
                    <x-ui::questionnaire.error />
                </x-ui::questionnaire.item>
                <x-ui::questionnaire.actions>
                    <x-ui::questionnaire.previous />
                    <x-ui::questionnaire.skip />
                    <x-ui::questionnaire.next />
                    <x-ui::questionnaire.submit />
                </x-ui::questionnaire.actions>
            </x-ui::questionnaire>
            BLADE);

        $view->assertSeeInOrder([
            '<form data-slot="questionnaire" novalidate="novalidate" data-shortcuts="letters" wire:submit="save">',
            'role="progressbar" aria-label="Questionnaire progress" aria-live="polite" aria-valuemin="1" aria-valuemax="2" aria-valuenow="1" data-slot="questionnaire-progress" data-template="Question :current of :total" wire:ignore>Question 1 of 2</div>',
            '<fieldset data-slot="questionnaire-item" data-name="direction" data-required="data-required" data-active="data-active" tabindex="-1">',
            '<legend data-slot="questionnaire-title">What next?</legend>',
            '<p data-slot="questionnaire-description" wire:key="questionnaire-description">Choose one.</p>',
            '<eso-radio-group role="radiogroup" name="direction" data-slot="questionnaire-choices" wire:model="direction">',
            '<label data-slot="questionnaire-choice" data-type="radio">',
            '<input type="radio" value="a" data-slot="questionnaire-choice-input">',
            '<span data-slot="questionnaire-choice-description">About A</span>',
            '<input type="text" name="direction" data-slot="questionnaire-input" aria-label="Other">',
            '<p data-slot="questionnaire-error" wire:key="questionnaire-error" hidden="hidden">Choose an answer to continue.</p>',
            '<fieldset data-slot="questionnaire-item" data-name="detail" data-multiple="data-multiple" hidden="hidden" inert="inert" tabindex="-1">',
            '<eso-checkbox-group role="group" name="detail[]" data-slot="questionnaire-choices">',
            '<input type="checkbox" value="x" data-slot="questionnaire-choice-input">',
            '<p data-slot="questionnaire-error" wire:key="questionnaire-error" hidden="hidden">Choose an answer or skip this question.</p>',
            '<div data-slot="questionnaire-actions">',
            'data-questionnaire="previous" hidden="hidden" inert="inert">Previous</button>',
            'data-questionnaire="skip" hidden="hidden" inert="inert">Skip</button>',
            'data-questionnaire="next">Next</button>',
            '<button type="submit" data-slot="button" data-variant="default" data-size="default" data-questionnaire="submit" hidden="hidden" inert="inert">Submit</button>',
        ], false);
    });

    it('works out which actions the opening item needs', function () {
        $items = [['name' => 'a', 'required' => true], 'b', 'c'];

        expect(QuestionnaireSteps::visible('previous', $items))->toBeFalse()
            ->and(QuestionnaireSteps::visible('skip', $items))->toBeFalse()
            ->and(QuestionnaireSteps::visible('next', $items))->toBeTrue()
            ->and(QuestionnaireSteps::visible('submit', $items))->toBeFalse()
            ->and(QuestionnaireSteps::visible('previous', $items, 'c'))->toBeTrue()
            ->and(QuestionnaireSteps::visible('skip', $items, 'c'))->toBeTrue()
            ->and(QuestionnaireSteps::visible('submit', $items, 'c'))->toBeTrue()
            ->and(QuestionnaireSteps::visible('next', []))->toBeFalse();
    });
});
