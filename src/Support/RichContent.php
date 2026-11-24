<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class RichContent
{
    private const MAX_RENDERED_HTML_BYTES = 8_000_000;

    public static function render(mixed $value, string $inputType = 'auto'): string
    {
        if (! is_string($value) && $value !== null) {
            throw new InvalidArgumentException(__('ui::errors.rich_text_value'));
        }
        if (! in_array($inputType, ['auto', 'html', 'markdown'], true)) {
            throw new InvalidArgumentException(__('ui::errors.rich_text_format'));
        }
        $content = $value ?? '';
        if (strlen($content) > ContentSchema::definition()['maxLength']) {
            throw new InvalidArgumentException(__('ui::errors.rich_text_length'));
        }
        if ($inputType === 'auto') {
            $inputType = preg_match('/^\s*<(?:[a-z][^>]*|!--)[>\s]/i', $content) ? 'html' : 'markdown';
        }
        if ($inputType === 'markdown') {
            static $markdown;
            $markdown ??= new GithubFlavoredMarkdownConverter([
                'html_input' => 'allow',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 64,
                // Shared sanitization handles supported HTML, without business media policies.
                'disallowed_raw_html' => ['disallowed_tags' => []],
            ]);
            $content = (string) $markdown->convert($content);
        }
        // The sanitizer truncates oversized HTML, so reject it before parsing a partial document.
        if (strlen($content) > self::MAX_RENDERED_HTML_BYTES) {
            throw new InvalidArgumentException(__('ui::errors.rich_text_length'));
        }

        return self::sanitizer()->sanitize($content);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        static $sanitizer;
        if ($sanitizer !== null) {
            return $sanitizer;
        }
        $schema = ContentSchema::definition();
        $config = new HtmlSanitizerConfig()
            ->allowRelativeLinks()->allowRelativeMedias()
            ->allowMediaSchemes(['https', 'http'])
            ->withMaxInputLength(self::MAX_RENDERED_HTML_BYTES)
            ->withAttributeSanitizer(new ContentAttributes);
        $tags = array_unique(array_merge(...array_values([...$schema['nodes'], ...$schema['marks']])));
        foreach ($tags as $tag) {
            $config = $config->allowElement($tag, [...$schema['globalAttributes'], ...($schema['attributes'][$tag] ?? [])]);
        }
        $config = $config
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->forceAttribute('input', 'type', 'checkbox')->forceAttribute('input', 'disabled', 'disabled');

        return $sanitizer = new HtmlSanitizer($config, new RichContentParser);
    }
}
