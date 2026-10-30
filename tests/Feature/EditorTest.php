<?php

use Dom\Element;
use Dom\HTMLDocument;
use Esoboy\Ui\Facades\Ui;
use Esoboy\Ui\Rules\RichTextLength;
use Esoboy\Ui\Support\EditorUploads;
use Esoboy\Ui\Support\RichText;
use Esoboy\Ui\Tests\Fixtures\Posts;
use Esoboy\Ui\Tests\Fixtures\Uploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\ViewException;
use Livewire\Livewire;

/*
 * Editor: Tiptap in the editor.js module, writing what its format allows, with HTML
 * sanitized to that format on the way in (the view) and, by the app, back (Ui::sanitize()).
 */

// The controls a toolbar shows (not those in its menus and popovers), by what names them.
function controls(string $html): array
{
    $document = HTMLDocument::createFromString("<!DOCTYPE html><body>{$html}</body>", LIBXML_NOERROR);
    $controls = $document->querySelectorAll('[data-slot="editor-toolbar"] :is([data-editor-command], [data-editor-action], [data-editor-opens], [data-editor-menu])');

    return array_values(array_map(
        fn (Element $control) => $control->getAttribute('data-editor-menu') ?? $control->getAttribute('data-editor-opens') ?? $control->getAttribute('data-editor-command') ?? ($control->getAttribute('data-editor-action') === 'media' ? $control->getAttribute('data-editor-value') : $control->getAttribute('data-editor-action')),
        array_filter(iterator_to_array($controls), fn (Element $control) => $control->closest('[popover]') === null),
    ));
}

describe('formats', function () {
    it('resolves presets, lists of features and presets, and named formats', function () {
        config()->set('ui.editor.formats', ['comment' => ['features' => ['bold', 'italic', 'link', 'emoji']]]);

        $text = RichText::resolve('text');
        $basic = RichText::resolve('basic');

        expect($text->has('heading', 'bold', 'color', 'font-size', 'text-indent', 'task-list', 'blockquote', 'link'))->toBeTrue()
            ->and($text->has('image', 'emoji', 'table'))->toBeFalse()
            ->and(array_values(array_diff($basic->features, $text->features)))->toBe(['emoji', 'image', 'audio', 'video', 'file', 'embed', 'slash-menu', 'drag-handle'])
            ->and(array_values(array_diff(RichText::resolve('full')->features, $basic->features)))->toBe(['code-block', 'table', 'details', 'math', 'callout', 'layout', 'source'])
            ->and(RichText::resolve()->features)->toBe(array_values(array_diff(RichText::FEATURES, ['typography'])))
            ->and(RichText::resolve(['link', 'bold', 'link'])->features)->toBe(['bold', 'link'])
            ->and(RichText::resolve(['basic', 'table'])->has('table', 'image', 'bold'))->toBeTrue()
            ->and(RichText::resolve('comment')->features)->toBe(['bold', 'italic', 'link', 'emoji'])
            ->and(RichText::resolve(['comment', 'image'])->has('image', 'bold'))->toBeTrue();
    });

    it('refuses what it does not know', function (string|array $format, string $message) {
        expect(fn () => RichText::resolve($format))->toThrow(InvalidArgumentException::class, $message);
    })->with([
        'a format' => ['short', '[short] is not a rich text format'],
        'a feature' => [['bold', 'blink'], '[blink] is not a rich text feature'],
    ]);

    it('keeps preset names for presets', function () {
        config()->set('ui.editor.formats', ['basic' => ['features' => ['bold']]]);

        RichText::resolve('text');
    })->throws(InvalidArgumentException::class, '[basic] is a preset');

    it('decides the tools too: the HTML source only in full, typography only when named', function () {
        expect(RichText::resolve('text')->has('toolbar', 'bubble-menu', 'find-replace', 'fullscreen', 'character-count', 'format-painter', 'markdown'))->toBeTrue()
            ->and(RichText::resolve('text')->has('slash-menu', 'drag-handle', 'source'))->toBeFalse()
            ->and(RichText::resolve('basic')->has('source'))->toBeFalse()
            ->and(RichText::resolve('full')->has('source'))->toBeTrue()
            ->and(RichText::resolve('full')->has('typography'))->toBeFalse()
            ->and(RichText::resolve(['full', 'typography'])->has('typography'))->toBeTrue()
            ->and(RichText::resolve(['bold'])->has('toolbar'))->toBeFalse()
            ->and(fn () => RichText::resolve(['bold', 'spellcheck']))->toThrow(InvalidArgumentException::class, '[spellcheck] is not a rich text feature');
    });
});

describe('the view', function () {
    beforeEach(fn () => config()->set('ui.editor.uploader', Uploads::class));

    it('renders the editor with its format and sanitized content', function () {
        $html = (string) $this->blade('<x-ui::editor wire:model="body" placeholder="Write" :max-length="500" default-value="<p onclick=\'x\'>Hi <b>there</b></p><script>alert(1)</script>" />');

        expect($html)
            ->toContain('<eso-editor data-slot="editor" data-format="{&quot;features&quot;:[&quot;heading&quot;,')
            ->toContain('&quot;layout&quot;,&quot;toolbar&quot;,&quot;bubble-menu&quot;,&quot;slash-menu&quot;,&quot;drag-handle&quot;,&quot;find-replace&quot;,&quot;source&quot;,')
            ->toContain('data-labels="{&quot;expand&quot;:&quot;Expand&quot;,&quot;collapse&quot;:&quot;Collapse&quot;,&quot;task&quot;:&quot;Done&quot;,&quot;caption&quot;:&quot;Write a caption…&quot;,&quot;failed&quot;:&quot;The upload failed.&quot;,&quot;keyboard&quot;:&quot;Alt+F10 moves to the toolbar or the menu by the selection; Escape comes back.&quot;}" data-upload="http://localhost/esoboy-ui/editor/upload" data-image-hosts="localhost" data-max-length="500" data-emoji="http://localhost/esoboy-ui/emoji/en.json?id=')
            ->toContain('&quot;allow&quot;:{&quot;a&quot;:{&quot;href&quot;:&quot;*&quot;,&quot;target&quot;:[&quot;_blank&quot;,&quot;_self&quot;],&quot;rel&quot;:&quot;*&quot;,&quot;title&quot;:&quot;*&quot;,&quot;download&quot;:&quot;*&quot;,&quot;type&quot;:&quot;*&quot;,&quot;style&quot;:0},')
            ->toContain('&quot;section&quot;:{&quot;style&quot;:1}')
            ->toContain('data-placeholder="Write" data-label="Rich text editor" data-editor-name="body" wire:model="body">')
            ->toContain('<div data-slot="editor-toolbar" role="toolbar" aria-label="Formatting" wire:ignore>')
            ->toContain('<div data-slot="editor-control" data-priority="1">')
            ->toContain('data-editor-command="bold" aria-label="Bold" aria-pressed="false" data-editor-keys="Mod-b"><svg')
            ->toContain('data-editor-action="media" data-editor-value="image" aria-label="Image"><svg')
            ->toContain('command="toggle-popover" commandfor="" data-editor-more="data-editor-more" hidden="hidden" aria-label="More"')
            ->toContain('data-editor-more-content="data-editor-more-content" aria-label="More"')
            ->toContain('<div data-slot="editor-find" role="search" aria-label="Find and replace" hidden wire:ignore>')
            ->toContain('<div data-slot="editor-content" wire:ignore><p>Hi <b>there</b></p></div>')
            ->toContain('<textarea data-slot="editor-source" aria-label="HTML source" spellcheck="false" hidden wire:ignore></textarea>')
            ->toContain('<div data-slot="editor-footer" wire:ignore>')
            ->toContain('<span data-editor-unsaved data-editor-label="Images not on this site: :count" hidden><span data-editor-unsaved-label></span>')
            ->toContain('data-editor-action="save-images">Save all</button>')
            ->toContain('<span data-editor-characters><span data-editor-count></span> characters</span>')
            ->toContain('data-slot="tooltip-content" data-side="top" data-align="center" data-editor-tip')
            ->not->toContain('onclick')
            ->not->toContain('alert(1)')
            ->and(controls($html))->toBe(['undo', 'redo', 'heading', 'font-family', 'font-size', 'bold', 'italic', 'underline', 'strike', 'color', 'code', 'superscript', 'subscript', 'clear-formatting', 'format-painter', 'link', 'text-align', 'line-height', 'spacing', 'outdent', 'indent', 'text-indent', 'bullet-list', 'ordered-list', 'task-list', 'blockquote', 'callout', 'code-block', 'horizontal-rule', 'image', 'video', 'audio', 'file', 'table', 'emoji', 'math', 'details', 'find', 'source', 'fullscreen']);
    });

    it('ranks its controls, the least used going into More first', function () {
        $toolbar = strstr((string) $this->blade('<x-ui::editor />'), 'data-slot="editor-content"', true);

        preg_match_all('~data-priority="(\d)">\s*<button[^>]*?data-editor-(?:command|action|opens|menu)="([\w-]+)"(?: data-editor-value="([\w-]+)")?~', $toolbar, $controls, PREG_SET_ORDER);

        $ranked = [];

        foreach ($controls as $control) {
            $ranked[(int) $control[1]][] = ($control[3] ?? '') !== '' && $control[2] === 'media' ? $control[3] : $control[2];
        }

        expect($ranked[1])->toBe(['undo', 'redo', 'heading', 'bold', 'italic', 'link', 'image']);
    });

    it('fits its toolbar and menus to the format', function () {
        $text = (string) $this->blade('<x-ui::editor format="text" />');
        $basic = (string) $this->blade('<x-ui::editor format="basic" />');
        $comment = (string) $this->blade('<x-ui::editor :format="[\'bold\', \'link\', \'toolbar\', \'bubble-menu\']" />');

        expect(controls($text))->toBe(['undo', 'redo', 'heading', 'font-family', 'font-size', 'bold', 'italic', 'underline', 'strike', 'color', 'code', 'superscript', 'subscript', 'clear-formatting', 'format-painter', 'link', 'text-align', 'line-height', 'spacing', 'outdent', 'indent', 'text-indent', 'bullet-list', 'ordered-list', 'task-list', 'blockquote', 'horizontal-rule', 'find', 'fullscreen'])
            ->and($text)->not->toContain('data-template="slash"')->not->toContain('data-template="drag-handle"')->not->toContain('data-emoji')->not->toContain('data-slot="editor-source"')->not->toContain('data-editor-unsaved')
            ->and(array_slice(controls($basic), -9))->toBe(['blockquote', 'horizontal-rule', 'image', 'video', 'audio', 'file', 'emoji', 'find', 'fullscreen'])
            ->and($basic)->toContain('data-template="slash"')->toContain('data-template="drag-handle"')->not->toContain('data-slot="editor-source"')
            ->and(controls($comment))->toBe(['undo', 'redo', 'bold', 'clear-formatting', 'link'])
            ->and($comment)->toContain('<template data-template="bubble-text">')
            ->not->toContain('data-template="bubble-table"')
            ->not->toContain('data-template="slash"')
            ->not->toContain('data-slot="editor-find"');
    });

    it('lists in its slash menu the blocks and media the format has', function () {
        $html = (string) $this->blade('<x-ui::editor :format="[\'heading\', \'bullet-list\', \'image\', \'video\', \'math\', \'toolbar\', \'slash-menu\']" />');

        preg_match('~<template data-template="slash">(.*?)</template>~s', $html, $slash);
        preg_match_all('~data-keywords="([^"]+)"~', $slash[1], $items);

        expect($items[1])->toBe(['paragraph', 'heading-1', 'heading-2', 'heading-3', 'heading-4', 'heading-5', 'heading-6', 'bullet-list', 'image', 'video', 'math'])
            ->and($slash[1])->toContain('data-editor-action="media" data-editor-value="image"')
            ->toContain('data-editor-action="open" data-editor-value="video"')
            ->toContain('data-editor-action="open" data-editor-value="math"');
    });

    it('offers the palette, fonts, sizes and line heights', function () {
        $html = (string) $this->blade('<x-ui::editor :format="[\'heading\', \'color\', \'highlight\', \'font-family\', \'font-size\', \'line-height\', \'toolbar\']" />');

        expect(substr_count($html, 'data-editor-command="color"'))->toBe(9)
            ->and(substr_count($html, 'data-editor-command="highlight"'))->toBe(9)
            ->and(substr_count($html, 'data-editor-command="font-family"'))->toBe(13)
            ->and(substr_count($html, 'data-editor-command="font-size"'))->toBe(13)
            ->and(substr_count($html, 'data-editor-command="line-height"'))->toBe(8)
            ->and($html)->toContain('data-editor-command="color" data-editor-value="#dc2626" aria-pressed="false" aria-label="Red"><span style="color: #dc2626"><svg')
            ->toContain('role="menuitemradio" data-slot="dropdown-menu-item" data-variant="default" aria-checked="false" data-editor-command="font-family" data-editor-value="SimSun, &#039;Songti SC&#039;, serif" data-editor-label="SimSun"')
            ->toContain('data-editor-command="heading" data-editor-value="2" data-editor-label="Heading 2"')
            ->toContain('data-editor-command="heading" data-editor-value="6" data-editor-label="Heading 6"')
            ->toContain('data-editor-menu="font-family" aria-label="Font"><span data-editor-current data-editor-default="Font">Font</span>')
            ->and(substr_count($html, 'data-editor-command="heading"'))->toBe(6);
    });

    it('gives its script the whitelist the server filters with (the copy the JS tests use)', function () {
        $fixture = json_decode((string) file_get_contents(__DIR__.'/../Fixtures/editor-full.json'), true);
        $keys = ['features', 'allow', 'css', 'callouts', 'styles', 'spacings', 'letterSpacings'];

        expect(array_intersect_key($fixture, array_flip($keys)))->toBe(array_intersect_key(RichText::resolve('full')->client(), array_flip($keys)));
    });

    it('offers spacing, callouts, a link card, captions, image links, cell colors and an upload placeholder', function () {
        config()->set('ui.editor.uploader', Uploads::class);

        $html = (string) $this->blade('<x-ui::editor />');

        expect(substr_count($html, 'data-editor-command="margin-top"'))->toBe(6)
            ->and(substr_count($html, 'data-editor-command="letter-spacing"'))->toBe(6)
            ->and(substr_count($html, 'data-editor-command="callout"'))->toBe(6)
            ->and($html)->toContain('data-editor-command="callout" data-editor-value="background-color:rgba(59,130,246,0.1);border-left:4px solid #3b82f6" data-editor-label="Note"')
            ->toContain('<template data-template="bubble-link">')
            ->toContain('<a data-slot="editor-link-href" data-editor-link-href target="_blank" rel="noopener noreferrer" title="Open link"></a>')
            ->toContain('data-editor-command="caption" aria-label="Caption" aria-pressed="false"')
            ->toContain('data-editor-image-link-input')
            ->toContain('data-editor-command="cell-background" data-editor-value="#fef08a"')
            ->toContain('data-editor-command="cell-align" data-editor-value="middle"')
            ->toContain('<template data-template="uploading">')
            ->toContain('data-editor-action="format-painter" aria-label="Format painter" aria-pressed="false"');
    });

    it('saves and shapes media from their menu, and takes videos uploaded or from a link', function () {
        config()->set('ui.editor.uploader', Uploads::class);

        $html = (string) $this->blade('<x-ui::editor />');

        preg_match('~<template data-template="bubble-node">(.*?)</template>~s', $html, $node);

        expect($node[1])->not->toContain('rotate')
            ->toContain('data-editor-action="save-image" aria-label="Save to this site" hidden="hidden"')
            ->toContain('data-editor-menu="embed-ratio" aria-label="Shape" data-editor-for="embed"')
            ->toContain('data-editor-command="embed-ratio" data-editor-value="9 / 16" data-editor-label="9:16"')
            ->toContain('<p data-slot="editor-field-error" data-editor-image-error role="alert" hidden></p>')
            ->and($html)->toContain('data-editor-opens="video" aria-label="Video"')
            ->toContain('data-editor-action="media" data-editor-value="video" data-editor-upload="data-editor-upload">')
            ->toContain('data-editor-video-input')
            ->toContain('placeholder="A video file’s address, a share link or embed code"');
    });

    it('takes a toolbar of its own, its controls rendering only when the format has them', function () {
        $html = (string) $this->blade(<<<'BLADE'
            <x-ui::editor format="text">
                <x-ui::editor.toolbar class="justify-end">
                    <x-ui::editor.control name="undo" />
                    <x-ui::editor.control name="bold" />
                    <x-ui::editor.separator />
                    <x-ui::editor.control name="fullscreen" />
                </x-ui::editor.toolbar>
            </x-ui::editor>
            BLADE);

        expect($html)->toContain('<div data-slot="editor-toolbar" role="toolbar" aria-label="Formatting" class="justify-end" wire:ignore>')
            ->toContain('data-editor-more="data-editor-more" hidden="hidden"')
            ->and(controls($html))->toBe(['undo', 'bold', 'fullscreen']);
    });

    it('refuses a button or a menu it does not have', function (string $blade, string $message) {
        $this->blade($blade);
    })->throws(ViewException::class)->with([
        'a button' => ['<x-ui::editor.button command="blink" />', '[blink] is not an Editor button'],
        'a menu' => ['<x-ui::editor.menu name="blink" />', '[blink] is not an Editor menu'],
    ]);

    it('keeps the HTML in a hidden input for forms, and shows the disabled state', function () {
        $this->blade('<x-ui::editor name="body" default-value="<p>Hi</p>" disabled />')
            ->assertSee('data-name="body" data-editor-name="body" data-disabled="data-disabled"', false)
            ->assertSee('<input type="hidden" name="body" value="&lt;p&gt;Hi&lt;/p&gt;">', false);
    });

    it('validates inside a field', function () {
        $this->withViewErrors(['body' => 'Write something.'])
            ->blade('<x-ui::field><x-ui::editor id="body" wire:model="body" /></x-ui::field>')
            ->assertSee('aria-invalid="true" aria-describedby="body-error" id="body" wire:model="body">', false)
            ->assertSeeInOrder(['</eso-editor>', 'Write something.'], false);
    });

    it('translates its labels', function () {
        app()->setLocale('zh_CN');

        $this->blade('<x-ui::editor />')
            ->assertSee('data-label="富文本编辑器"', false)
            ->assertSee('aria-label="格式"', false)
            ->assertSee('aria-label="粗体"', false)
            ->assertSee('placeholder="粘贴链接…"', false)
            ->assertSee('&quot;expand&quot;:&quot;展开&quot;', false)
            ->assertSee('>宋体</span>', false);
    });

    it('loads its module once per page', function () {
        Route::get('/editors', fn () => Blade::render('<html><head></head><body><x-ui::editor /><x-ui::editor /></body></html>'));

        $html = $this->get('/editors')->getContent();

        expect(substr_count($html, '<script type="module" src="http://localhost/esoboy-ui/editor.js?id=7777dddd8888"></script>'))->toBe(1);
    });

    it('renders what a Livewire property holds, sanitized', function () {
        Livewire::test(Posts::class)
            ->call('load', '<p>Hi<img src="x" onerror="alert(1)"></p>')
            ->assertSet('body', '<p>Hi<img src="x" onerror="alert(1)"></p>')
            ->assertSeeHtml('<div data-slot="editor-content" wire:ignore><p>Hi<img src="x" /></p></div>');
    });

    it('shows rich text as written, sanitized to its format', function () {
        $this->blade('<x-ui::rich-text class="prose" html="<h2 style=\'color:red;position:fixed\'>Hi</h2><p>a<script>x</script></p>" />')
            ->assertSee('<div data-slot="rich-text" class="prose"><h2 style="color:red" id="hi">Hi</h2><p>a</p></div>', false);

        $this->blade('<x-ui::rich-text format="text" html="<h2>Hi</h2><p><b>a</b><img src=\'/a.png\'></p>" />')
            ->assertSee('<div data-slot="rich-text"><h2 id="hi">Hi</h2><p><b>a</b></p></div>', false);
    });

    it('gives headings ids to link to, numbered when they repeat, and lets images load as they come into view', function () {
        $html = '<h2>Getting started</h2><h3>第一章 安装</h3><h2>Getting  started</h2><h2>!!!</h2><p><img src="/a.png" width="10" height="10"></p>';

        expect(RichText::display($html))->toBe('<h2 id="getting-started">Getting started</h2><h3 id="第一章-安装">第一章 安装</h3><h2 id="getting-started-2">Getting  started</h2><h2 id="section">!!!</h2><p><img src="/a.png" width="10" height="10" loading="lazy" decoding="async"></p>')
            ->and(RichText::headings($html))->toBe([
                ['level' => 2, 'text' => 'Getting started', 'id' => 'getting-started'],
                ['level' => 3, 'text' => '第一章 安装', 'id' => '第一章-安装'],
                ['level' => 2, 'text' => 'Getting started', 'id' => 'getting-started-2'],
                ['level' => 2, 'text' => '!!!', 'id' => 'section'],
            ])
            ->and(RichText::display(''))->toBe('')
            ->and(RichText::headings('<p>none</p>'))->toBe([]);
    });

    it('lists the headings as an anchor, the top three levels it has, each under the one before', function () {
        $html = '<h2>A</h2><h3>B</h3><h4>C</h4><h5>D</h5><h3>E</h3>';
        $toc = (string) $this->blade('<x-ui::rich-text.toc :html="$html" class="sticky">On this page</x-ui::rich-text.toc>', ['html' => $html]);

        expect($toc)->toContain('<eso-anchor role="navigation" aria-label="On this page" data-slot="anchor" class="sticky">')
            ->toContain('<p data-slot="anchor-title">On this page</p>')
            ->toContain('<a data-slot="anchor-item" data-level="1" href="#a">A</a>')
            ->toContain('<a data-slot="anchor-item" data-level="2" href="#b">B</a>')
            ->toContain('<a data-slot="anchor-item" data-level="3" href="#c">C</a>')
            ->toContain('<a data-slot="anchor-item" data-level="2" href="#e">E</a>')
            ->not->toContain('#d')
            ->and(trim((string) $this->blade('<x-ui::rich-text.toc html="<p>No headings</p>" />')))->toBe('');
    });
});

describe('images side by side', function () {
    it('keeps a row of images and its grid, and offers it from an image\'s menu', function () {
        $row = '<div style="display:grid;grid-template-columns:repeat(2, minmax(0, 1fr));gap:8px;align-items:start"><img src="/a.png" style="display:block;width:100%" /><a href="/b"><img src="/b.png" /></a></div>';

        expect(RichText::sanitize($row, 'basic'))->toBe('<div style="display:grid;grid-template-columns:repeat(2, minmax(0, 1fr));gap:8px;align-items:start"><img src="/a.png" style="display:block;width:100%" /><a href="/b" rel="noopener noreferrer nofollow"><img src="/b.png" /></a></div>');

        preg_match('~<template data-template="bubble-node">(.*?)</template>~s', (string) $this->blade('<x-ui::editor format="basic" />'), $node);

        expect($node[1])->toContain('data-editor-command="image-row" aria-label="Side by side" aria-pressed="false"');
    });
});

describe('counting', function () {
    it('counts characters as the editor does: graphemes, no spaces or line breaks, no media, formulas or file cards', function () {
        $html = '<p>汉字 👍🏽</p><p>a b<br>c'."\u{3000}".'d</p><img src="/a.png"><p><math><semantics><mi>x</mi><annotation encoding="application/x-tex">x</annotation></semantics></math></p><a href="/f.pdf" download="f.pdf">f.pdf</a>';

        expect(RichText::characters($html))->toBe(7)
            ->and(Ui::characters($html))->toBe(7)
            ->and(RichText::characters(''))->toBe(0)
            ->and(RichText::characters('<p>a<script>bcdef</script></p>', 'text'))->toBe(1);
    });

    it('validates rich text by that count, not by its HTML', function () {
        $long = '<p style="color:#dc2626;font-size:18px"><strong>'.str_repeat('字', 5).'</strong></p>';
        $validate = fn (string $html, RichTextLength $rule) => Validator::make(['body' => $html], ['body' => [$rule]]);

        expect($validate($long, new RichTextLength(max: 5))->passes())->toBeTrue()
            ->and($validate($long, new RichTextLength(max: 4))->errors()->first('body'))->toBe('The body may not be longer than 4 characters (it has 5).')
            ->and($validate($long, new RichTextLength(min: 6))->errors()->first('body'))->toBe('The body must be at least 6 characters (it has 5).')
            ->and($validate('<p>a</p><img src="/a.png">', new RichTextLength(max: 1, format: 'basic'))->passes())->toBeTrue();
    });
});

describe('sanitizing', function () {
    it('keeps what the editor writes', function () {
        $html = '<h1>A</h1><h2>B</h2><h3>C</h3><h4>D</h4><h5>E</h5><h6>F</h6><p>Text <strong>b</strong> <em>i</em> <u>u</u> <s>s</s> <code>c</code> <sup>2</sup> <sub>2</sub><br />line</p>'
            .'<ul><li><p>One</p></li></ul><ol start="3"><li><p>Three</p></li></ol><blockquote><p>Q</p></blockquote><pre><code>a &lt; b</code></pre><hr />'
            .'<details><summary>More</summary><div><p>Hidden</p></div></details>';

        expect(RichText::sanitize($html))->toBe($html);
    });

    it('keeps the whitelisted CSS properties of a style, their values as written', function () {
        $html = '<p style="text-align: center; margin-left: 4em; text-indent: 2em; line-height: 1.75;">'
            .'<span style="color: rgb(220, 38, 38); font-family: SimSun, &quot;Songti SC&quot;, serif; font-size: 18px;">a</span> '
            .'<mark style="background-color: rgb(254, 240, 138); color: rgb(23, 23, 23);">b</mark></p>';

        expect(RichText::sanitize($html))->toBe('<p style="text-align:center;margin-left:4em;text-indent:2em;line-height:1.75"><span style="color:rgb(220, 38, 38);font-family:SimSun, &#34;Songti SC&#34;, serif;font-size:18px">a</span> <mark style="background-color:rgb(254, 240, 138);color:rgb(23, 23, 23)">b</mark></p>');
    });

    it('drops CSS that could cover the page, run or load something', function () {
        expect(RichText::sanitize('<p style="text-align: start; position: fixed; z-index: 9">a <span style="color: #123456; font-size: 17px; background: url(javascript:x); background-image: url(/a.png); width: expression(alert(1))">b</span></p>'))
            ->toBe('<p style="text-align:start">a <span style="color:#123456;font-size:17px;background-image:url(/a.png)">b</span></p>')
            ->and(RichText::sanitize('<p style="color: red; position: fixed; background-image: linear-gradient(rgba(0,0,0,.5), red)">a</p><p style="color: red; x: url(a(b); position: fixed">b</p>'))
            ->toBe('<p style="color:red;background-image:linear-gradient(rgba(0,0,0,.5), red)">a</p><p style="color:red">b</p>');
    });

    it('keeps the tags of the format, each as the one whitelist says: attributes, values, CSS', function () {
        $html = '<h2 style="text-align:center;position:fixed">A</h2><p style="text-indent:2em;border:1px solid red;z-index:9"><span style="color:#dc2626;font-size:18px;transform:rotate(3deg)"><strong>b</strong></span> <a href="/x" target="_top">l</a> <a href="/y" target="_blank">m</a></p><ol type="Q" start="3"><li>x</li></ol><center class="c" id="i">c</center>';

        expect(RichText::sanitize($html, ['heading', 'font-size']))->toBe('<h2 style="text-align:center">A</h2><p style="text-indent:2em;border:1px solid red"><span style="color:#dc2626;font-size:18px">b</span> l m</p>xc')
            ->and(RichText::sanitize($html, 'text'))->toBe('<h2 style="text-align:center">A</h2><p style="text-indent:2em;border:1px solid red"><span style="color:#dc2626;font-size:18px"><strong>b</strong></span> <a href="/x" rel="noopener noreferrer nofollow">l</a> <a href="/y" target="_blank" rel="noopener noreferrer nofollow">m</a></p><ol start="3"><li>x</li></ol>c')
            ->and(Ui::sanitize($html, 'full'))->toEndWith('<ol start="3"><li>x</li></ol><center>c</center>')
            ->and(RichText::resolve('text')->whitelist()['a'])->toBe(['href' => '*', 'target' => ['_blank', '_self'], 'rel' => '*', 'title' => '*', 'download' => '*', 'type' => '*', 'style' => RichText::resolve('text')->whitelist()['span']['style']])
            ->and(RichText::resolve('text')->whitelist())->not->toHaveKey('img');
    });

    it('takes tags, attributes and values the app adds, for the formats that name the tags', function () {
        config()->set('ui.editor.whitelist', ['img' => ['data-ratio' => '*'], 'span' => ['style' => ['transform']], 'center' => '*']);
        config()->set('ui.editor.formats', ['notes' => ['features' => ['bold'], 'tags' => ['center']]]);

        $html = '<p><span style="color:red;transform:rotate(3deg)"><strong>b</strong></span><img src="/a.png" data-ratio="0.5" data-x="1"></p><center class="c" id="i" onclick="x()" data-y="1">c</center>';

        expect(RichText::sanitize($html, 'notes'))->toBe('<p><strong>b</strong></p><center class="c" id="i">c</center>')
            ->and(RichText::sanitize($html, 'full'))->toBe('<p><span style="color:red;transform:rotate(3deg)"><strong>b</strong></span><img src="/a.png" data-ratio="0.5" /></p><center class="c" id="i">c</center>')
            ->and(RichText::resolve('notes')->client()['allow']['center'])->toBe('*')
            ->and(RichText::resolve('notes')->client()['attributes'])->toContain('class', 'id')->not->toContain('onclick')
            ->and(function () {
                config()->set('ui.editor.formats', ['wrong' => ['features' => ['bold'], 'tags' => ['marquee']]]);
                RichText::resolve('wrong');
            })->toThrow(InvalidArgumentException::class, '[marquee] is not in the whitelist')
            ->and(function () {
                config()->set('ui.editor.whitelist', ['p' => ['onclick' => '*']]);
                RichText::resolve('text');
            })->toThrow(InvalidArgumentException::class, '[onclick] runs script');
    });

    it('keeps captions, image links and callouts', function () {
        $html = '<figure style="margin: 0"><a href="https://x.test"><img src="/a.png" style="width: 50%"></a><figcaption style="text-align: center">Cap</figcaption></figure><aside style="background-color: rgba(59,130,246,0.1); border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 6px"><p>Note</p></aside>';

        expect(RichText::sanitize($html))->toBe('<figure style="margin:0"><a href="https://x.test" rel="noopener noreferrer nofollow"><img src="/a.png" style="width:50%" /></a><figcaption style="text-align:center">Cap</figcaption></figure><aside style="background-color:rgba(59,130,246,0.1);border-left:4px solid #3b82f6;padding:12px 16px;border-radius:6px"><p>Note</p></aside>');
    });

    it('keeps task lists as disabled boxes, never fields', function () {
        $html = '<ul style="list-style-type: none; padding-left: 0px;"><li style="display: flex; align-items: baseline; gap: 0.5em;"><input type="text" name="admin" value="1" checked="checked" form="f" /><div><p>Done</p></div></li></ul>';

        expect(RichText::sanitize($html))->toBe('<ul style="list-style-type:none;padding-left:0px"><li style="display:flex;align-items:baseline;gap:0.5em"><input checked="checked" type="checkbox" disabled="disabled" /><div><p>Done</p></div></li></ul>')
            ->and(RichText::sanitize('<p><input type="text" name="admin" value="1"></p>', 'basic'))->toBe('<p><input type="checkbox" disabled="disabled" /></p>')
            ->and(RichText::sanitize('<p><input type="text" name="admin"></p>', ['bold']))->toBe('<p></p>');
    });

    it('keeps media and files on web addresses, as players, and data: images until uploaded', function () {
        $html = '<img src="/a.png" alt="A" width="1200" height="800" style="display: block; width: 50%; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;">'
            .'<img src="data:image/png;base64,AAAA" style="width: 50%"><img src="data:image/svg+xml;base64,PHN2Zz4="><img src="javascript:alert(1)">'
            .'<video src="https://cdn.test/v.mp4" poster="/p.jpg" autoplay onplay="x()" style="display: block; width: 480px;"></video><video src="/v.mp4" poster="data:image/png;base64,AAAA"></video>'
            .'<audio src="/a.mp3" title="Song" loop></audio><audio src="data:audio/wav;base64,AA"></audio>'
            .'<a href="/f.pdf" download="f.pdf" type="application/pdf" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(128, 128, 128, 0.4); border-radius: 8px; color: inherit; text-decoration: none;"><span style="flex-shrink: 0;">📎</span><span style="min-width: 0px; overflow-wrap: anywhere;">f.pdf</span><span style="flex-shrink: 0; white-space: nowrap; opacity: 0.6;">1 KB</span></a>';

        expect(RichText::sanitize($html))->toBe(
            '<img src="/a.png" alt="A" width="1200" height="800" style="display:block;width:50%;max-width:100%;height:auto;margin-left:auto;margin-right:auto" />'
            .'<img src="data:image/png;base64,AAAA" style="width:50%" />'
            .'<video src="https://cdn.test/v.mp4" poster="/p.jpg" style="display:block;width:480px" controls="controls" preload="metadata"></video><video src="/v.mp4" controls="controls" preload="metadata"></video>'
            .'<audio src="/a.mp3" title="Song" controls="controls" preload="metadata"></audio>'
            .'<a href="/f.pdf" download="f.pdf" type="application/pdf" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(128, 128, 128, 0.4);border-radius:8px;color:inherit;text-decoration:none" rel="noopener noreferrer nofollow"><span style="flex-shrink:0">📎</span><span style="min-width:0px;overflow-wrap:anywhere">f.pdf</span><span style="flex-shrink:0;white-space:nowrap;opacity:0.6">1 KB</span></a>'
        )
            ->and(RichText::sanitize('<p>a</p><img src="data:image/png;base64,AAAA">', ['bold']))->toBe('<p>a</p>');
    });

    it('keeps a file card whole in a format without layout', function () {
        $card = '<a href="/f.pdf" download="f.pdf" style="display:flex;gap:8px"><span style="flex-shrink:0">📎</span><span style="min-width:0px;overflow-wrap:anywhere">f.pdf</span></a>';

        expect(RichText::sanitize($card, ['file']))->toBe('<a href="/f.pdf" download="f.pdf" style="display:flex;gap:8px" rel="noopener noreferrer nofollow"><span style="flex-shrink:0">📎</span><span style="min-width:0px;overflow-wrap:anywhere">f.pdf</span></a>');
    });

    it('plays embeds from known platforms only', function () {
        config()->set('ui.editor.embed_platforms', [
            'acme' => ['src' => 'https://player.acme.test/$1', 'pattern' => '^https://acme\.test/v/(\w+)'],
            'code' => ['src' => 'https://embed.code.test/'],
        ]);

        $style = 'style="display: block; width: 100%; max-width: 100%; height: auto; aspect-ratio: 16 / 9; border: 0px;"';

        expect(RichText::sanitize("<iframe src=\"https://www.youtube-nocookie.com/embed/abc\" {$style} onload=\"x()\"></iframe><iframe src=\"https://player.acme.test/1\"></iframe><iframe src=\"https://embed.code.test/v?id=2\"></iframe><iframe src=\"https://evil.test/x\"></iframe><iframe src=\"http://player.bilibili.com/player.html\"></iframe>"))
            ->toBe('<iframe src="https://www.youtube-nocookie.com/embed/abc?autoplay&#61;0" style="display:block;width:100%;max-width:100%;height:auto;aspect-ratio:16 / 9;border:0px" allowfullscreen="allowfullscreen" loading="lazy"></iframe><iframe src="https://player.acme.test/1?autoplay&#61;0" allowfullscreen="allowfullscreen" loading="lazy"></iframe><iframe src="https://embed.code.test/v?id&#61;2&amp;autoplay&#61;0" allowfullscreen="allowfullscreen" loading="lazy"></iframe>')
            ->and(RichText::sanitize('<iframe src="https://player.bilibili.com/player.html?bvid=BV1&amp;autoplay=1#t=3"></iframe>'))->toBe('<iframe src="https://player.bilibili.com/player.html?bvid&#61;BV1&amp;autoplay&#61;0#t&#61;3" allowfullscreen="allowfullscreen" loading="lazy"></iframe>')
            ->and(RichText::still('https://v.qq.com/txp/iframe/player.html?vid=x&autoplay=true'))->toBe('https://v.qq.com/txp/iframe/player.html?vid=x&autoplay=false&auto=0')
            ->and(RichText::sanitize('<iframe src="https://www.youtube-nocookie.com/embed/abc"></iframe><p>a</p>', 'text'))->toBe('<p>a</p>')
            ->and(RichText::resolve()->client()['platforms'])->toContain(['https://open.iqiyi.com/', null, 'autoplay=false'], ['https://embed.code.test/', null, 'autoplay=0']);
    });

    it('keeps tables with their widths and cell styles', function () {
        $cell = 'border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top;';
        $html = "<div style=\"overflow-x: auto;\"><table style=\"border-collapse: collapse; width: 360px;\"><colgroup><col style=\"width: 120px;\" /><col style=\"min-width: 48px;\" /></colgroup><tbody><tr><th colspan=\"2\" style=\"{$cell} background-color: rgba(128, 128, 128, 0.1); text-align: left;\"><p>H</p></th></tr><tr><td style=\"{$cell} color: red;\"><p>1</p></td><td style=\"{$cell}\"><p>2</p></td></tr></tbody></table></div>";

        expect(RichText::sanitize($html))->toBe('<div style="overflow-x:auto"><table style="border-collapse:collapse;width:360px"><colgroup><col style="width:120px" /><col style="min-width:48px" /></colgroup><tbody><tr><th colspan="2" style="border:1px solid rgba(128, 128, 128, 0.4);padding:6px 10px;vertical-align:top;background-color:rgba(128, 128, 128, 0.1);text-align:left"><p>H</p></th></tr><tr><td style="border:1px solid rgba(128, 128, 128, 0.4);padding:6px 10px;vertical-align:top;color:red"><p>1</p></td><td style="border:1px solid rgba(128, 128, 128, 0.4);padding:6px 10px;vertical-align:top"><p>2</p></td></tr></tbody></table></div>')
            ->and(RichText::sanitize('<table style="border-collapse: collapse; border: 1px solid red"><tbody><tr><td style="padding: 4px; color: red"><p>1</p></td></tr></tbody></table>', ['table']))
            ->toBe('<table style="border-collapse:collapse;border:1px solid red"><tbody><tr><td style="padding:4px;color:red"><p>1</p></td></tr></tbody></table>');
    });

    it('keeps formulas as MathML, their LaTeX with them', function () {
        $math = '<math display="block"><semantics><mrow><msup><mi mathvariant="normal">e</mi><mn>2</mn></msup></mrow><annotation encoding="application/x-tex">\mathrm{e}^2</annotation></semantics></math>';

        expect(RichText::sanitize($math))->toBe($math)
            ->and(RichText::sanitize('<p>a</p>'.$math, 'basic'))->toBe('<p>a</p>');
    });

    it('keeps web, mail and phone links, made safe', function () {
        expect(RichText::sanitize('<p><a href="https://laravel.com" target="_blank">a</a> <a href="mailto:x@y.dev">b</a> <a href="tel:+861">c</a> <a href="/docs">d</a> <a href="javascript:alert(1)">e</a> <a href="data:text/html,x">f</a></p>'))
            ->toBe('<p><a href="https://laravel.com" target="_blank" rel="noopener noreferrer nofollow">a</a> <a href="mailto:x&#64;y.dev" rel="noopener noreferrer nofollow">b</a> <a href="tel:&#43;861" rel="noopener noreferrer nofollow">c</a> <a href="/docs" rel="noopener noreferrer nofollow">d</a> <a rel="noopener noreferrer nofollow">e</a> <a rel="noopener noreferrer nofollow">f</a></p>');
    });

    it('drops scripts, frames and attributes not whitelisted, and unwraps tags not whitelisted keeping their text', function () {
        $html = '<p class="x" style="color:red" onclick="steal()">Hi <span data-a="1">there</span><img src="x" onerror="alert(1)"></p><script>alert(2)</script><iframe src="https://evil.test"></iframe><svg><circle /></svg><section><div>kept</div></section><form><button>Go</button></form>';

        expect(RichText::sanitize($html))->toBe('<p style="color:red">Hi there<img src="x" /></p><section><div>kept</div></section>')
            ->and(RichText::sanitize($html, 'basic'))->toBe('<p style="color:red">Hi there<img src="x" /></p><div>kept</div>');
    });

    it('turns nothing into an empty string and keeps long HTML whole', function () {
        $long = str_repeat('<p>abcdefghij</p>', 3000);

        expect(RichText::sanitize(null))->toBe('')
            ->and(RichText::sanitize('   '))->toBe('')
            ->and(RichText::sanitize($long))->toBe($long)
            ->and(Ui::sanitize('<p>a<script>x</script></p>'))->toBe('<p>a</p>');
    });

    it('gives the text of rich text', function () {
        $html = '<h2>Title</h2><p>One<br>two <strong>bold</strong> &amp; more</p><ul><li><p>Item</p></li></ul><table><tbody><tr><td><p>a</p></td><td><p>b</p></td></tr></tbody></table><p>Formula <math><semantics><mi>x</mi><annotation encoding="application/x-tex">x^2</annotation></semantics></math></p><script>alert(1)</script>';

        expect(Ui::plainText($html))->toBe('Title
One
two bold & more
Item
a	b
Formula x^2')
            ->and(Ui::plainText('<section><span>Title</span><p>Body</p></section>'))->toBe('Title
Body')
            ->and(Ui::plainText(null))->toBe('');
    });

    it('leaves what the browser sends to the app, which sanitizes it before storing it', function () {
        Livewire::test(Posts::class)
            ->set('body', '<p onclick="steal()">Hi</p><script>alert(1)</script>')
            ->assertSet('body', '<p onclick="steal()">Hi</p><script>alert(1)</script>')
            ->assertSeeHtml('<div data-slot="editor-content" wire:ignore><p>Hi</p></div>')
            ->call('save')
            ->assertSet('saved', '<p>Hi</p>');
    });
});

describe('the server', function () {
    it('inserts attachments or HTML through Ui::editor(), by the name its wire:model gives it, in its component', function () {
        $posts = Livewire::test(Posts::class);

        expect($posts->html())->toContain('data-editor-name="body"');

        $posts->call('attach')
            ->assertDispatched('ui-editor', name: 'body', owner: $posts->instance()->getId(), action: 'insert', attachments: [['url' => '/a.png', 'name' => 'a.png', 'mime' => 'image/png']], html: null)
            ->call('template')
            ->assertDispatched('ui-editor', name: 'body', owner: $posts->instance()->getId(), action: 'insert-content', attachments: [], html: '<p>Hello</p>');
    });

    it('takes the editor\'s name over its wire:model', function () {
        $this->blade('<x-ui::editor name="intro" wire:model="body" />')
            ->assertSee('data-name="intro" data-editor-name="intro"', false);
    });
});

describe('uploads', function () {
    // A picture, as a site would send it.
    $png = fn () => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
    $post = fn (array $data) => test()->post('/esoboy-ui/editor/upload', $data, ['Accept' => 'application/json']);

    beforeEach(function () {
        Uploads::$uploads = [];
        config()->set('ui.editor.uploader', Uploads::class);
    });

    it('offers no media buttons, saving or placeholders to upload with, without an uploader', function () {
        config()->set('ui.editor.uploader', null);

        $html = (string) $this->blade('<x-ui::editor :upload-context="[\'post\' => 7]" />');

        preg_match('~<template data-template="missing-image">(.*?)</template>~s', $html, $missing);

        expect($html)->not->toContain('data-upload')
            ->not->toContain('data-image-hosts')
            ->not->toContain('data-editor-action="media"')
            ->not->toContain('data-editor-action="save-image"')
            ->not->toContain('data-editor-unsaved')
            ->not->toContain('<template data-template="uploading">')
            ->toContain('data-editor-opens="video"')
            ->toContain('data-editor-video-input')
            ->and($missing[1])->toContain('This picture couldn’t be pasted')->not->toContain('upload-missing')
            ->and(controls($html))->not->toContain('image', 'audio', 'file');
    });

    it('tells the editor where to upload, the app’s hosts, its sealed context and what goes up by itself', function () {
        config()->set('ui.editor.hosts', ['cdn.app.test', 'https://img.app.test:8443/x', '*.oss.test']);
        config()->set('ui.editor.auto_upload', ['external' => true, 'data' => false]);
        config()->set('app.asset_url', 'https://assets.app.test');
        config()->set('filesystems.disks.s3.url', 'https://bucket.s3.test/files');

        $html = (string) $this->blade('<x-ui::editor :upload-context="[\'post\' => 7]" />');

        preg_match('~data-upload-context="([^"]+)"~', $html, $context);

        expect($html)->toContain('data-upload="http://localhost/esoboy-ui/editor/upload"')
            ->toContain('data-image-hosts="cdn.app.test img.app.test:8443 *.oss.test localhost assets.app.test bucket.s3.test"')
            ->toContain('data-auto-upload="external"')
            ->toContain('<template data-template="upload-failed">')
            ->and(EditorUploads::open(html_entity_decode($context[1])))->toBe(['post' => 7])
            ->and(controls($html))->toContain('image', 'audio', 'file');
    });

    it('hands each file to the uploader, with where it came from and the page’s context, and answers with its attachment', function () use ($post) {
        $post(['source' => 'pick', 'file' => UploadedFile::fake()->create('a.pdf', 3, 'application/pdf'), 'context' => EditorUploads::seal(['post' => 7])])
            ->assertOk()
            ->assertExactJson(['attachment' => ['url' => '/uploads/a.pdf', 'name' => 'a.pdf', 'mime' => 'application/pdf', 'size' => 3072]]);

        $post(['source' => 'paste', 'file' => UploadedFile::fake()->create('empty.png', 1, 'image/png')])->assertExactJson(['attachment' => null]);

        expect(array_map(fn ($upload) => [$upload->source, $upload->url, $upload->context], Uploads::$uploads))->toBe([['pick', null, ['post' => 7]], ['paste', null, []]]);
    });

    it('says why it won’t: the request, a changed context, an image that isn’t a picture, the uploader’s reason; nothing without an uploader', function () use ($post) {
        $post(['source' => 'stolen', 'file' => UploadedFile::fake()->create('a.png')])->assertUnprocessable()->assertJsonValidationErrors('source');
        $post(['source' => 'pick'])->assertUnprocessable()->assertJsonValidationErrors('file');
        $post(['source' => 'pick', 'file' => UploadedFile::fake()->create('a.png'), 'context' => 'forged'])->assertForbidden();
        $post(['source' => 'data', 'file' => UploadedFile::fake()->createWithContent('a.png', '<svg onload="x()"/>')])->assertUnprocessable()->assertJsonValidationErrors(['file' => 'This image couldn’t be read.']);
        $post(['source' => 'drop', 'file' => UploadedFile::fake()->image('refuse.png')])->assertUnprocessable()->assertJsonValidationErrors(['file' => 'Not this one.']);

        config()->set('ui.editor.uploader', null);

        $post(['source' => 'pick', 'file' => UploadedFile::fake()->image('a.png')])->assertNotFound();
        expect(array_map(fn ($upload) => $upload->file->getClientOriginalName(), Uploads::$uploads))->toBe(['refuse.png']);
    });

    it('fetches an image on another site the browser couldn’t read, as a file for the uploader', function () use ($post, $png) {
        Http::fake([
            '93.184.216.34/old/cat.png' => Http::response('', 302, ['Location' => '/pics/cat.png']),
            '93.184.216.34/pics/cat.png' => Http::response($png(), 200, ['Content-Type' => 'application/octet-stream']),
        ]);

        $post(['source' => 'external', 'url' => 'http://93.184.216.34/old/cat.png'])
            ->assertOk()
            ->assertJsonPath('attachment.url', '/uploads/cat.png')
            ->assertJsonPath('attachment.mime', 'image/png');

        expect(Uploads::$uploads[0]->url)->toBe('http://93.184.216.34/old/cat.png')
            ->and(Uploads::$uploads[0]->source)->toBe('external')
            ->and(is_file(Uploads::$uploads[0]->file->getPathname()))->toBeFalse();
    });

    it('fetches nothing from the server’s own network, through redirects too, nor what isn’t a picture', function (string $url) use ($post, $png) {
        Http::fake([
            '93.184.216.34/inside' => Http::response('', 302, ['Location' => 'http://10.0.0.1/a.png']),
            '93.184.216.34/page' => Http::response('<html></html>', 200, ['Content-Type' => 'image/png']),
            '*' => Http::response($png(), 200),
        ]);

        $post(['source' => 'external', 'url' => $url])->assertUnprocessable()->assertJsonValidationErrors(['file' => 'This image couldn’t be read.']);

        expect(Uploads::$uploads)->toBe([]);
        Http::assertSentCount(str_starts_with($url, 'http://93.184.216.34/') ? 1 : 0);
    })->with([
        'localhost' => 'http://127.0.0.1/a.png',
        'cloud metadata' => 'http://169.254.169.254/latest/meta-data',
        'a cloud’s shared network' => 'http://100.100.100.200/latest/meta-data',
        'a private network' => 'http://10.0.0.1/a.png',
        'IPv6 loopback' => 'http://[::1]/a.png',
        'IPv4 in IPv6' => 'http://[::ffff:127.0.0.1]/a.png',
        'a name for none' => 'http://unresolvable.invalid/a.png',
        'a file' => 'file:///etc/passwd',
        'credentials' => 'http://user:secret@93.184.216.34/a.png',
        'a redirect inside' => 'http://93.184.216.34/inside',
        'a page' => 'http://93.184.216.34/page',
    ]);
});
