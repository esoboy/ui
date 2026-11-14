<?php

namespace Esoboy\Ui\Support;

use Dom\HTMLElement;
use Dom\Node;
use Dom\Text;
use Tempest\Highlight\Highlighter;

final class CodeHighlight
{
    private const MAX_HIGHLIGHT_BYTES = 128 * 1024;

    /** @return list<list<array{string, ?string}>> */
    public static function lines(string $code, ?string $language): array
    {
        $code = str_replace(["\r\n", "\r"], "\n", $code);
        $language = strtolower(trim($language ?? ''));
        $language = ['sh' => 'bash', 'shell' => 'bash', 'shellscript' => 'bash', 'zsh' => 'bash'][$language] ?? $language;

        if ($language !== '' && strlen($code) <= self::MAX_HIGHLIGHT_BYTES) {
            $highlighter = new Highlighter;
            if (in_array($language, $highlighter->getSupportedLanguageNames(), true)) {
                $document = HtmlFragment::parse($highlighter->parse($code, $language));
                // A grammar may transform its input. Copying must always reproduce the source.
                if ($document->body?->textContent === $code) {
                    $lines = [[]];
                    self::collect($document->body, null, $lines);

                    return $lines;
                }
            }
        }

        return array_map(fn (string $text) => $text === '' ? [] : [[$text, null]], explode("\n", $code));
    }

    public static function compound(string $slot, string $language): string
    {
        $document = HtmlFragment::parse($slot);
        $rows = iterator_to_array($document->querySelectorAll('[data-slot="code-block-line-content"]'));
        $code = implode("\n", array_map(fn (Node $row) => $row->textContent === "\n" ? '' : $row->textContent, $rows));
        $lines = self::lines($code, $language);
        if (count($lines) === count($rows)) {
            foreach ($rows as $index => $row) {
                $tokens = $lines[$index];
                if ($row instanceof HTMLElement && array_filter($tokens, fn (array $token) => $token[1] !== null)) {
                    $row->innerHTML = view('ui::partials.code-tokens', compact('tokens'))->render();
                }
            }
        }

        $body = $document->body;

        return $body === null ? $slot : $body->innerHTML;
    }

    /** @param list<list<array{string, ?string}>> $lines */
    private static function collect(Node $node, ?string $class, array &$lines): void
    {
        if ($node instanceof Text) {
            foreach (explode("\n", $node->data) as $index => $part) {
                if ($index > 0) {
                    $lines[] = [];
                }
                if ($part !== '') {
                    $lines[array_key_last($lines)][] = [$part, $class];
                }
            }

            return;
        }

        if ($node instanceof HTMLElement && $node->localName === 'span') {
            $class = $node->getAttribute('class') ?? $class;
        }
        foreach ($node->childNodes as $child) {
            self::collect($child, $class, $lines);
        }
    }
}
