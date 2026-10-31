<?php

namespace Esoboy\Ui\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Keeps what the whitelist says of an attribute: one of its values (any for '*'); for
 * style, the declarations whose property it names, their values as written unless they
 * could run or load something (expression(), javascript:, a url() that isn't http(s) or
 * relative, escapes, comments). Embeds' players only on their hosts, never playing by
 * themselves; data: addresses only as an image's picture (PNG, JPEG, GIF, WebP, BMP,
 * AVIF: not SVG, which holds markup).
 */
final class RichTextAttributes implements AttributeSanitizerInterface
{
    // The attributes that hold an address (as Symfony's UrlAttributeSanitizer has them).
    private const ADDRESSES = ['src', 'href', 'lowsrc', 'background', 'ping', 'action', 'formaction', 'poster', 'cite', 'data', 'codebase', 'archive', 'longdesc'];

    /**
     * @param  array<string, '*'|array<string, '*'|list<string>>>  $whitelist
     * @param  list<string>  $hosts
     */
    public function __construct(
        private readonly array $whitelist,
        private readonly array $hosts,
    ) {}

    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): ?array
    {
        return null;
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $rules = $this->whitelist[$element] ?? [];
        $values = $rules === '*' ? '*' : ($rules[$attribute] ?? '*');

        if (in_array($attribute, self::ADDRESSES, true) && str_starts_with(strtolower(trim($value)), 'data:')) {
            return $element === 'img' && $attribute === 'src' && preg_match('~^data:image/(?:png|jpeg|jpg|gif|webp|bmp|avif);base64,[a-z0-9+/]++={0,2}$~i', $value) ? $value : null;
        }

        if ($element === 'iframe' && $attribute === 'src') {
            return $this->plays($value) ? RichText::still($value) : null;
        }

        if ($attribute === 'style') {
            return self::style($value, $values);
        }

        return $values === '*' || in_array(strtolower(trim($value)), array_map('strtolower', $values), true) ? $value : null;
    }

    /**
     * The declarations of a style kept: their property among these ('*' for any), their
     * value safe.
     *
     * @param  '*'|list<string>  $properties
     */
    private static function style(string $style, string|array $properties): ?string
    {
        $kept = [];

        foreach (self::declarations($style) as [$property, $declared]) {
            if (($properties === '*' || in_array($property, $properties, true)) && self::safe($declared)) {
                $kept[$property] = "{$property}:{$declared}";
            }
        }

        return $kept === [] ? null : implode(';', $kept);
    }

    /**
     * A style's declarations as [property, value], split on the semicolons outside
     * parentheses and quotes.
     *
     * @return list<array{string, string}>
     */
    public static function declarations(string $style): array
    {
        $parts = [];
        $part = '';
        $depth = 0;
        $quote = null;

        foreach (mb_str_split($style) as $character) {
            if ($quote !== null) {
                $quote = $character === $quote ? null : $quote;
            } elseif ($character === '"' || $character === "'") {
                $quote = $character;
            } elseif ($character === '(') {
                $depth++;
            } elseif ($character === ')') {
                $depth = max(0, $depth - 1);
            } elseif ($character === ';' && $depth === 0) {
                $parts[] = $part;
                $part = '';

                continue;
            }

            $part .= $character;
        }

        $declarations = [];

        foreach ([...$parts, $part] as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);
            $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

            // Unbalanced, a value could carry a declaration of its own: it goes.
            if ($value !== '' && ! str_contains($value, ';')) {
                $declarations[] = [strtolower(trim($property)), $value];
            }
        }

        return $declarations;
    }

    private static function safe(string $value): bool
    {
        if (preg_match('~expression\s*\(|javascript:|vbscript:|-moz-binding|behavior|@import|[\\\\<>{}]|/\*~i', $value)) {
            return false;
        }

        preg_match_all('~url\(\s*([\'"]?)(.*?)\1\s*\)~i', $value, $urls);

        foreach ($urls[2] as $url) {
            $scheme = parse_url(trim($url), PHP_URL_SCHEME);

            if (is_string($scheme) && ! in_array(strtolower($scheme), ['http', 'https'], true)) {
                return false;
            }
        }

        return true;
    }

    private function plays(string $src): bool
    {
        return parse_url($src, PHP_URL_SCHEME) === 'https' && in_array(parse_url($src, PHP_URL_HOST), $this->hosts, true);
    }
}
