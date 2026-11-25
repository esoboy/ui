<?php

namespace Esoboy\Ui\Support;

use Dom\Element;
use Dom\Node;
use Symfony\Component\HtmlSanitizer\Parser\NativeParser;
use Symfony\Component\HtmlSanitizer\Parser\ParserInterface;

final class RichContentParser implements ParserInterface
{
    public function parse(string $html, string $context = 'body'): ?Node
    {
        $root = (new NativeParser)->parse($html, $context);
        if (! $root instanceof Element) {
            return $root;
        }

        $policy = ContentSchema::definition()['iframe'];
        foreach ($root->querySelectorAll('iframe') as $frame) {
            $src = trim($frame->getAttribute('src') ?? '');
            if (str_starts_with($src, '//')) {
                $src = 'https:'.$src;
            }
            $parts = parse_url($src);
            if (! preg_match('~^https?://[^/?#@]+(?:[/?#]|$)~i', $src) || preg_match('~[\s<>]~u', $src) || str_contains($src, '\\')
                || ! is_array($parts) || ! isset($parts['scheme'], $parts['host']) || $parts['host'] === '' || isset($parts['user']) || isset($parts['pass'])) {
                $frame->remove();

                continue;
            }
            $origin = strtolower($parts['scheme'].'://'.$parts['host']);
            if (isset($parts['port']) && $parts['port'] !== 443) {
                $origin .= ':'.$parts['port'];
            }
            $frame->setAttribute('src', $src);
            $frame->setAttribute('sandbox', $policy['sandbox'].(in_array($origin, $policy['playerOrigins'], true) ? ' allow-same-origin' : ''));
            $frame->setAttribute('referrerpolicy', $policy['attributes']['referrerpolicy']);
            if ($frame->getAttribute('allow') !== $policy['attributes']['allow']) {
                $frame->removeAttribute('allow');
            }
        }

        return $root;
    }
}
