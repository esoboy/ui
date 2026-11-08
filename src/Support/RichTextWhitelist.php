<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

/**
 * The tags rich text may have, as UEditor's whitelist: each with the attributes kept on
 * it, and for each attribute the values kept ('*' for any; for style, the CSS properties
 * kept, their values as written unless they could run or load something). A tag of '*'
 * keeps every attribute the HTML standard has, with any value. Formats pick tags from
 * here; the app adds tags, attributes and values in config ui.editor.whitelist.
 */
final class RichTextWhitelist
{
    // CSS by what it sets: text, a block's text, its box, flex and grid layout (images
    // side by side), lists, tables.
    private const TEXT = ['color', 'background-color', 'font', 'font-family', 'font-size', 'font-style', 'font-weight', 'font-variant', 'text-decoration', 'text-decoration-line', 'text-decoration-style', 'text-decoration-color', 'text-transform', 'text-shadow', 'letter-spacing', 'word-spacing', 'line-height', 'white-space', 'word-break', 'overflow-wrap', 'word-wrap', 'vertical-align', 'direction'];

    private const PARAGRAPH = ['text-align', 'text-indent'];

    private const BOX = [
        'display', 'width', 'min-width', 'max-width', 'height', 'min-height', 'max-height', 'box-sizing', 'aspect-ratio', 'object-fit',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'border', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-width', 'border-style', 'border-color',
        'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width', 'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style', 'border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color',
        'border-radius', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-right-radius', 'border-bottom-left-radius',
        'background', 'background-color', 'background-image', 'background-size', 'background-position', 'background-repeat',
        'box-shadow', 'opacity', 'overflow', 'overflow-x', 'overflow-y', 'float', 'clear',
    ];

    private const FLEX = ['flex', 'flex-direction', 'flex-wrap', 'flex-grow', 'flex-shrink', 'flex-basis', 'justify-content', 'align-items', 'align-self', 'gap', 'row-gap', 'column-gap', 'grid-template-columns'];

    private const LIST = ['list-style', 'list-style-type', 'list-style-position'];

    private const TABLE = ['border-collapse', 'border-spacing', 'table-layout', 'caption-side'];

    // Blocks of text, inline text, media and embeds, table parts.
    private const BLOCK = ['style' => [...self::TEXT, ...self::PARAGRAPH, ...self::BOX, ...self::FLEX]];

    private const INLINE = ['style' => [...self::TEXT, ...self::BOX, ...self::FLEX]];

    private const MEDIA = ['style' => self::BOX];

    private const CELL = ['colspan' => '*', 'rowspan' => '*', 'style' => [...self::TEXT, ...self::PARAGRAPH, ...self::BOX, ...self::TABLE]];

    /**
     * Every tag, with its attributes and their values. (MathML: KaTeX's, without style.)
     */
    public const RULES = [
        'p' => self::BLOCK, 'br' => [], 'div' => self::BLOCK,
        'h1' => self::BLOCK, 'h2' => self::BLOCK, 'h3' => self::BLOCK, 'h4' => self::BLOCK, 'h5' => self::BLOCK, 'h6' => self::BLOCK,
        'blockquote' => self::BLOCK, 'pre' => self::BLOCK, 'hr' => self::MEDIA,
        'strong' => self::INLINE, 'b' => self::INLINE, 'em' => self::INLINE, 'i' => self::INLINE, 'u' => self::INLINE, 's' => self::INLINE, 'del' => self::INLINE, 'ins' => self::INLINE,
        'code' => self::INLINE, 'sup' => self::INLINE, 'sub' => self::INLINE, 'mark' => self::INLINE, 'span' => self::INLINE, 'small' => self::INLINE, 'big' => self::INLINE,
        'abbr' => ['title' => '*', ...self::INLINE], 'cite' => self::INLINE, 'q' => self::INLINE, 'kbd' => self::INLINE, 'samp' => self::INLINE, 'var' => self::INLINE, 'dfn' => self::INLINE,
        'time' => ['datetime' => '*', ...self::INLINE], 'bdi' => self::INLINE, 'bdo' => ['dir' => ['ltr', 'rtl'], ...self::INLINE], 'font' => ['color' => '*', 'face' => '*', 'size' => '*', ...self::INLINE],
        'a' => ['href' => '*', 'target' => ['_blank', '_self'], 'rel' => '*', 'title' => '*', 'download' => '*', 'type' => '*', ...self::INLINE],
        'ul' => ['style' => [...self::BLOCK['style'], ...self::LIST]], 'ol' => ['start' => '*', 'type' => ['1', 'a', 'A', 'i', 'I'], 'style' => [...self::BLOCK['style'], ...self::LIST]], 'li' => ['style' => [...self::BLOCK['style'], ...self::LIST]],
        'input' => ['type' => ['checkbox'], 'checked' => '*', 'disabled' => '*'],
        'img' => ['src' => '*', 'alt' => '*', 'title' => '*', 'width' => '*', 'height' => '*', ...self::MEDIA],
        'figure' => self::BLOCK, 'figcaption' => self::BLOCK,
        'audio' => ['src' => '*', 'controls' => '*', 'preload' => ['none', 'metadata', 'auto'], 'title' => '*', ...self::MEDIA],
        'video' => ['src' => '*', 'poster' => '*', 'controls' => '*', 'preload' => ['none', 'metadata', 'auto'], 'width' => '*', 'height' => '*', 'title' => '*', ...self::MEDIA],
        'iframe' => ['src' => '*', 'title' => '*', 'allowfullscreen' => '*', 'loading' => ['lazy'], ...self::MEDIA],
        'table' => ['style' => [...self::BLOCK['style'], ...self::TABLE]], 'colgroup' => [], 'col' => ['span' => '*', ...self::MEDIA], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => self::BLOCK, 'th' => self::CELL, 'td' => self::CELL,
        'details' => self::BLOCK, 'summary' => self::BLOCK, 'aside' => self::BLOCK,
        'section' => self::BLOCK, 'article' => self::BLOCK, 'header' => self::BLOCK, 'footer' => self::BLOCK, 'main' => self::BLOCK, 'nav' => self::BLOCK, 'address' => self::BLOCK, 'center' => self::BLOCK,
        'dl' => self::BLOCK, 'dt' => self::BLOCK, 'dd' => self::BLOCK,
        'math' => ['display' => ['block', 'inline']], 'semantics' => [], 'annotation' => ['encoding' => '*'], 'mrow' => [], 'mi' => ['mathvariant' => '*'], 'mn' => ['mathvariant' => '*'],
        'mo' => ['stretchy' => '*', 'fence' => '*', 'separator' => '*', 'lspace' => '*', 'rspace' => '*', 'form' => '*', 'largeop' => '*', 'movablelimits' => '*', 'accent' => '*', 'minsize' => '*', 'maxsize' => '*', 'symmetric' => '*'],
        'ms' => [], 'mtext' => ['mathvariant' => '*'], 'mspace' => ['width' => '*', 'height' => '*', 'depth' => '*'], 'msup' => [], 'msub' => [], 'msubsup' => [], 'mfrac' => ['linethickness' => '*'], 'msqrt' => [], 'mroot' => [],
        'mstyle' => ['displaystyle' => '*', 'scriptlevel' => '*', 'mathcolor' => '*', 'mathbackground' => '*', 'mathsize' => '*'], 'munder' => ['accentunder' => '*'], 'mover' => ['accent' => '*'], 'munderover' => ['accent' => '*', 'accentunder' => '*'],
        'mtable' => ['columnalign' => '*', 'rowspacing' => '*', 'columnspacing' => '*', 'rowlines' => '*', 'columnlines' => '*', 'displaystyle' => '*'], 'mtr' => [], 'mtd' => ['columnalign' => '*', 'columnspan' => '*', 'rowspan' => '*'],
        'mpadded' => ['width' => '*', 'height' => '*', 'depth' => '*', 'lspace' => '*', 'voffset' => '*'], 'mphantom' => [], 'menclose' => ['notation' => '*'], 'merror' => [], 'mmultiscripts' => [], 'mprescripts' => [], 'none' => [],
    ];

    /** @var array{string, array<string, '*'|array<string, '*'|list<string>>>}|null */
    private static ?array $rules = null;

    /**
     * Every tag's rules, with what config ui.editor.whitelist adds ('*' for all):
     * ['section' => '*', 'img' => ['data-ratio' => '*'], 'span' => ['style' =>
     * ['transform']]]. A format keeps an added tag once it names it.
     *
     * @return array<string, '*'|array<string, '*'|list<string>>>
     */
    public static function rules(): array
    {
        /** @var array<string, '*'|array<string, '*'|list<string>>> $added */
        $added = (array) config('ui.editor.whitelist', []);
        $key = (string) json_encode($added);

        if (self::$rules !== null && self::$rules[0] === $key) {
            return self::$rules[1];
        }

        foreach ($added as $attributes) {
            foreach ($attributes === '*' ? [] : array_keys($attributes) as $attribute) {
                if (str_starts_with(strtolower($attribute), 'on')) {
                    throw new InvalidArgumentException("[{$attribute}] runs script; the whitelist never keeps it.");
                }
            }
        }

        return (self::$rules = [$key, self::merge(self::RULES, $added)])[1];
    }

    /**
     * Rules put together: '*' over anything, values joined.
     *
     * @param  array<string, '*'|array<string, '*'|list<string>>>  $rules
     * @param  array<string, '*'|array<string, '*'|list<string>>>  $more
     * @return array<string, '*'|array<string, '*'|list<string>>>
     */
    private static function merge(array $rules, array $more): array
    {
        foreach ($more as $tag => $attributes) {
            $known = $rules[$tag] ?? [];

            if ($attributes === '*' || $known === '*') {
                $rules[$tag] = '*';

                continue;
            }

            foreach ($attributes as $attribute => $values) {
                $had = $known[$attribute] ?? [];
                $known[$attribute] = $values === '*' || $had === '*' ? '*' : array_values(array_unique([...$had, ...$values]));
            }

            $rules[$tag] = $known;
        }

        return $rules;
    }
}
