<?php

namespace Esoboy\Ui\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Reference\W3CReference;

/**
 * A resolved rich text format (RichText::resolve()): its features, the tags they write
 * (and the tags a named format adds), each kept as the whitelist (RichTextWhitelist)
 * says; the sanitizer that keeps just that, and what the Editor's script needs.
 */
final class RichTextFormat
{
    // The inline styles the Editor writes for what it creates.
    public const CELL = 'border:1px solid rgba(128,128,128,0.4);padding:6px 10px;vertical-align:top';

    public const HEADER_CELL = 'background-color:rgba(128,128,128,0.1);text-align:left';

    public const CARD = 'display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(128,128,128,0.4);border-radius:8px;color:inherit;text-decoration:none';

    public const CALLOUT = 'padding:12px 16px;border-radius:6px';

    public const FIGURE = 'margin:0';

    public const CAPTION = 'margin-top:6px;text-align:center;font-size:0.875em;color:#6b7280';

    /** Paragraphs and line breaks: in every format. */
    private const BASE = ['p', 'br', 'div'];

    private const MATHML = ['math', 'semantics', 'annotation', 'mrow', 'mi', 'mn', 'mo', 'ms', 'mtext', 'mspace', 'msup', 'msub', 'msubsup', 'mfrac', 'msqrt', 'mroot', 'mstyle', 'munder', 'mover', 'munderover', 'mtable', 'mtr', 'mtd', 'mpadded', 'mphantom', 'menclose', 'merror', 'mmultiscripts', 'mprescripts', 'none'];

    /**
     * The tags each feature writes. (layout: those no feature writes, as pasted.)
     */
    private const TAGS = [
        'heading' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'bold' => ['strong', 'b'],
        'italic' => ['em', 'i'],
        'underline' => ['u'],
        'strike' => ['s', 'del'],
        'code' => ['code'],
        'superscript' => ['sup'],
        'subscript' => ['sub'],
        'link' => ['a'],
        'color' => ['span'],
        'highlight' => ['mark'],
        'font-family' => ['span'],
        'font-size' => ['span'],
        'bullet-list' => ['ul', 'li'],
        'ordered-list' => ['ol', 'li'],
        'task-list' => ['ul', 'li', 'input'],
        'blockquote' => ['blockquote'],
        'horizontal-rule' => ['hr'],
        'code-block' => ['pre', 'code'],
        'image' => ['img', 'figure', 'figcaption', 'a'],
        'audio' => ['audio'],
        'video' => ['video'],
        'file' => ['a', 'span'],
        'embed' => ['iframe'],
        'table' => ['table', 'colgroup', 'col', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td'],
        'details' => ['details', 'summary'],
        'math' => self::MATHML,
        'callout' => ['aside'],
        'layout' => ['section', 'article', 'aside', 'header', 'footer', 'main', 'nav', 'address', 'center', 'figure', 'figcaption', 'dl', 'dt', 'dd', 'span', 'small', 'big', 'abbr', 'cite', 'q', 'ins', 'kbd', 'samp', 'var', 'dfn', 'time', 'bdi', 'bdo', 'font'],
    ];

    private ?HtmlSanitizer $sanitizer = null;

    /**
     * @param  list<string>  $features
     * @param  list<string>  $tags  tags a named format adds
     */
    public function __construct(
        public readonly array $features,
        private readonly array $tags = [],
    ) {}

    /**
     * Whether it has any of these features.
     */
    public function has(string ...$features): bool
    {
        return array_intersect($features, $this->features) !== [];
    }

    /**
     * The tags kept, each with its rules from the whitelist: its attributes and their
     * values ('*' for any; for style, CSS properties), or '*' for all the standard's.
     *
     * @return array<string, '*'|array<string, '*'|list<string>>>
     */
    public function whitelist(): array
    {
        $tags = array_unique([...self::BASE, ...array_merge(...array_map(fn (string $feature) => self::TAGS[$feature] ?? [], $this->features)), ...$this->tags]);
        $whitelist = array_intersect_key(RichTextWhitelist::rules(), array_flip($tags));

        ksort($whitelist);

        return $whitelist;
    }

    /**
     * Cleans HTML to the whitelist. Images, players and embeds whose address didn't
     * pass go entirely, rather than stay as empty boxes; spans left without attributes
     * go too, their text kept. Images may be data: (pasted, not uploaded yet) when the
     * format has images.
     */
    public function sanitize(string $html): string
    {
        $html = ($this->sanitizer ??= new HtmlSanitizer($this->config()))->sanitize($html);
        $html = preg_replace('~<img(?![^>]* src=)[^>]*/>|<(iframe|video|audio)(?![^>]* src=)[^>]*></\1>~', '', $html) ?? '';

        do {
            $html = preg_replace('~<span>((?:(?!<span[\s>]).)*?)</span>~s', '$1', $html, -1, $count) ?? '';
        } while ($count);

        return $html;
    }

    /**
     * What the Editor's script reads from data-format: the features, the whitelist, and
     * the values its controls offer.
     *
     * @return array<string, mixed>
     */
    public function client(): array
    {
        // Each list of CSS properties once (tags share them), a tag's style its index.
        $css = [];
        $allow = array_map(function (string|array $rules) use (&$css) {
            if (is_array($rules) && is_array($rules['style'] ?? null)) {
                $index = array_search($rules['style'], $css, true);
                $rules['style'] = $index === false ? array_push($css, $rules['style']) - 1 : $index;
            }

            return $rules;
        }, $this->whitelist());

        return [
            'features' => $this->features,
            'allow' => $allow,
            'css' => $css,
            // What '*' keeps: the standard's attributes (never an event handler).
            'attributes' => in_array('*', $allow, true) ? array_keys(W3CReference::ATTRIBUTES) : [],
            'headings' => RichText::HEADINGS,
            'colors' => array_values(RichText::COLORS),
            'highlights' => array_values(RichText::HIGHLIGHTS),
            'highlightText' => RichText::HIGHLIGHT_TEXT,
            'fonts' => array_values(RichText::FONTS),
            'sizes' => RichText::SIZES,
            'lineHeights' => RichText::LINE_HEIGHTS,
            'indents' => RichText::INDENTS,
            'spacings' => RichText::SPACINGS,
            'letterSpacings' => RichText::LETTER_SPACINGS,
            'callouts' => RichText::CALLOUTS,
            'platforms' => array_values(RichText::platforms()),
            'styles' => ['cell' => self::CELL, 'headerCell' => self::HEADER_CELL, 'card' => self::CARD, 'callout' => self::CALLOUT, 'figure' => self::FIGURE, 'caption' => self::CAPTION],
        ];
    }

    private function config(): HtmlSanitizerConfig
    {
        $whitelist = $this->whitelist();

        $config = (new HtmlSanitizerConfig)
            ->defaultAction(HtmlSanitizerAction::Block)
            ->withMaxInputLength(-1)
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes($this->has('image') ? ['http', 'https', 'data'] : ['http', 'https'])
            ->allowRelativeMedias()
            ->withAttributeSanitizer(new RichTextAttributes($whitelist, $this->hosts()));

        foreach ($whitelist as $tag => $attributes) {
            $config = $config->allowElement($tag, $attributes === '*' ? '*' : array_keys($attributes));
        }

        if (isset($whitelist['a'])) {
            $config = $config->forceAttribute('a', 'rel', 'noopener noreferrer nofollow');
        }

        // A task's box, never a field of a form around the content.
        if (isset($whitelist['input'])) {
            $config = $config->forceAttribute('input', 'type', 'checkbox')->forceAttribute('input', 'disabled', 'disabled');
        }

        foreach (['audio', 'video'] as $media) {
            if (isset($whitelist[$media])) {
                $config = $config->forceAttribute($media, 'controls', 'controls')->forceAttribute($media, 'preload', 'metadata');
            }
        }

        if (isset($whitelist['iframe'])) {
            $config = $config->forceAttribute('iframe', 'allowfullscreen', 'allowfullscreen')->forceAttribute('iframe', 'loading', 'lazy');
        }

        // These go with what is inside them. (Head elements, style and title, can't be
        // dropped inside a body: their text stays, escaped.)
        foreach (['script', 'template', 'noscript', 'iframe', 'object', 'embed', 'svg', 'math', 'textarea', 'select', 'button', 'input'] as $tag) {
            if (! isset($whitelist[$tag])) {
                $config = $config->dropElement($tag);
            }
        }

        return $config;
    }

    /**
     * The hosts embeds may play from: their platforms' players.
     *
     * @return list<string>
     */
    private function hosts(): array
    {
        if (! $this->has('embed')) {
            return [];
        }

        return array_values(array_unique(array_map(fn (array $platform) => (string) parse_url($platform[0], PHP_URL_HOST), RichText::platforms())));
    }
}
