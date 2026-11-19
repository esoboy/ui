<?php

namespace Esoboy\Ui\Support;

use Dom\Document;
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

        $document = $root->ownerDocument;
        if ($document === null) {
            return $root;
        }
        $this->prepareFrames($root, $document);

        // Add the attachment's presentation to the tree before the shared sanitizer visits it.
        foreach ($root->querySelectorAll('a[data-type="file"][href]') as $file) {
            $title = $file->getAttribute('title') ?? '';
            $href = $file->getAttribute('href') ?? '';
            $name = $file->getAttribute('data-name') ?? '';
            if ($name === '') {
                $name = $file->getAttribute('download') ?: trim(($file->firstElementChild ?? $file)->textContent ?? '');
            }
            if ($name === '') {
                $name = rawurldecode(basename((string) parse_url($href, PHP_URL_PATH)));
            }
            $file->setAttribute('data-name', $name);
            $file->setAttribute('download', $name);
            $label = $document->createElement('span');
            $label->textContent = $title !== '' ? $title : ($name !== '' ? $name : ($href !== '' ? $href : __('ui::editor.file')));
            $file->replaceChildren($label);

            $info = [];
            if ($title !== '' && $name !== '' && $title !== $name) {
                $info[] = $name;
            }
            $size = $file->getAttribute('data-size');
            if ($size !== null && ctype_digit($size)) {
                $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                $unit = min(count($units) - 1, (int) floor(log(max(1, (float) $size), 1024)));
                $info[] = round((float) $size / 1024 ** $unit, 1).' '.$units[$unit];
            }
            if ($info !== []) {
                $meta = $document->createElement('span');
                $meta->textContent = implode(' · ', $info);
                $file->appendChild($meta);
            }
        }

        return $root;
    }

    private function prepareFrames(Element $root, Document $document): void
    {
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
            $clean = $document->createElement('iframe');
            $clean->setAttribute('src', $src);
            $title = trim($frame->getAttribute('title') ?? '');
            $clean->setAttribute('title', $title === '' ? __('ui::editor.embeddedContent') : $title);
            foreach (['width', 'height'] as $dimension) {
                $value = $frame->getAttribute($dimension) ?? '';
                $clean->setAttribute($dimension, preg_match('/^[1-9]\d{0,4}$/D', $value) ? $value : (string) $policy[$dimension]);
            }
            foreach ($policy['attributes'] as $name => $value) {
                $clean->setAttribute($name, $value);
            }
            $clean->setAttribute('sandbox', $policy['sandbox'].(in_array($origin, $policy['playerOrigins'], true) ? ' allow-same-origin' : ''));
            $frame->replaceWith($clean);
        }
    }
}
