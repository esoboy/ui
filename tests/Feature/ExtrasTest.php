<?php

use Dom\HTMLDocument;
use Livewire\Component;
use Livewire\Livewire;

/*
 * Status colours, Code Block, Trace, Questionnaire.
 */

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

        $view->assertSeeMarkupInOrder([
            '<div data-slot="code-block" data-variant="default" data-size="sm">',
            '<div data-slot="code-block-header">',
            '<div data-slot="code-block-title">app.php</div>',
            'data-code-block-copy="data-code-block-copy" aria-label="Copy" data-label-copy="Copy" data-label-copied="Copied"',
            '<span aria-live="polite" class="sr-only" data-code-block-status>',
            '<pre data-slot="code-block-content" data-language="php" data-numbered="data-numbered" dir="ltr" tabindex="0" style="counter-reset: line 9;">',
            '<code data-slot="code-block-code">',
            '<div data-slot="code-block-footer">PHP</div>',
        ]);
    });

    it('keeps source characters and blank lines when highlighting or falling back', function (string $language, string $code, bool $highlighted) {
        $html = (string) $this->blade('<x-ui::code-block.content :language="$language" :code="$code" />', compact('language', 'code'));
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $rows = $document->querySelectorAll('[data-slot="code-block-line-content"]');
        $text = implode("\n", array_map(fn ($row) => $row->textContent === "\n" ? '' : $row->textContent, iterator_to_array($rows)));

        expect($text)->toBe($code)
            ->and($document->querySelector('script'))->toBeNull()
            ->and($document->querySelector('[data-token]') !== null)->toBe($highlighted);
    })->with([
        ['php', "echo '<script>';\n\n// Comment", true],
        ['blade', '@if ($a) {{ $a }} @endif', true],
        ['js', 'const item = "<p>";', true],
        ['unknown-language', '<script>alert(1)</script>', false],
        ['php', str_repeat("echo 1;\n", 17000).'echo 2;', false],
    ]);

    it('takes hand-written rows for diffs', function () {
        $this->blade('<x-ui::code-block.content wrap><x-ui::code-block.line variant="removed">a</x-ui::code-block.line><x-ui::code-block.line variant="added" /></x-ui::code-block.content>')
            ->assertSeeMarkup('<pre data-slot="code-block-content" data-wrap="data-wrap" dir="ltr" tabindex="0" style="counter-reset: line 0;"><code data-slot="code-block-code"><span data-slot="code-block-line" data-variant="removed"><span data-slot="code-block-line-content">a</span></span>')
            ->assertSee("<span data-slot=\"code-block-line\" data-variant=\"added\"><span data-slot=\"code-block-line-content\">\n</span></span></code></pre>", false);
    });

    it('renders updated highlighting when a Livewire caller changes the code', function () {
        Livewire::test(new class extends Component
        {
            public string $code = 'echo 11;';

            public function render(): string
            {
                return '<div><x-ui::code-block.content language="php" :code="$code" /></div>';
            }
        })
            ->assertSee('data-token', false)
            ->assertSee(' 11;', false)
            ->set('code', 'echo 22;')
            ->assertSee(' 22;', false)
            ->assertDontSee(' 11;', false);
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

        $view->assertSeeMarkupInOrder([
            '<div data-slot="trace">',
            '<span data-slot="trace-line" data-status="running">',
            '<span data-slot="trace-icon" aria-hidden="true">i</span>',
            '<span data-slot="trace-title">Searching</span>',
            '<span data-slot="trace-description">0.4s</span>',
        ]);
    });

    it('collapses steps in a Collapsible, open by default when asked', function () {
        $template = '<x-ui::trace.collapsible :open="$open"><x-ui::trace.collapsible-trigger>Read</x-ui::trace.collapsible-trigger><x-ui::trace.collapsible-content>files</x-ui::trace.collapsible-content></x-ui::trace.collapsible>';

        $this->blade($template, ['open' => true])
            ->assertSeeMarkup('<eso-collapsible data-slot="trace-collapsible" data-open="data-open">')
            ->assertSeeMarkup('<button type="button" data-slot="trace-collapsible-trigger" data-collapsible-trigger="data-collapsible-trigger" aria-expanded="true">')
            ->assertSeeMarkup('<div data-slot="trace-collapsible-content" data-collapsible-content >')
            ->assertSeeMarkup('<div data-slot="trace-collapsible-content-inner">files</div>');

        $this->blade($template, ['open' => false])
            ->assertSee('aria-expanded="false"', false)
            ->assertSeeMarkup('<div data-slot="trace-collapsible-content" hidden>');
    });
});

describe('questionnaire', function () {
    it('renders questions, choices, shortcuts and actions in PHP', function () {
        $html = (string) $this->blade('<x-ui::questionnaire shortcuts="letters" :items="[[\'name\' => \'direction\', \'title\' => \'What next?\', \'description\' => \'Choose one.\', \'required\' => true, \'options\' => [\'a\' => \'A\']], [\'name\' => \'detail\', \'title\' => \'Details?\', \'multiple\' => true, \'options\' => [\'x\' => \'X\']]]" />');
        expect($html)->toContain('Question 1 of 2')->toContain('What next?')->toContain('Choose one.')->toContain('data-shortcut="A"')->toContain('wire:click="choose(')->toContain('wire:click="next"')->toContain('data-name="detail"')->not->toContain('<eso-radio-group');
    });
});
