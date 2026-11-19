<?php

namespace Esoboy\Ui\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

final class ContentAttributes implements AttributeSanitizerInterface
{
    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): array
    {
        return ['style', 'src', 'class', 'target', 'colwidth', 'data-align', 'data-display-size', 'data-type'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        if ($attribute === 'style') {
            $styles = [];
            foreach (explode(';', $value) as $declaration) {
                $parts = explode(':', $declaration, 2);
                if (count($parts) !== 2) {
                    continue;
                }
                $name = strtolower(trim($parts[0]));
                $content = trim($parts[1]);
                $pattern = ContentSchema::definition()['styles'][$name] ?? null;
                if ($pattern !== null && preg_match('~'.$pattern.'~iuD', $content) === 1) {
                    $styles[] = $name.': '.$content;
                }
            }

            return $styles === [] ? null : implode('; ', $styles);
        }

        return match ($attribute) {
            'class' => preg_match('/^language-[a-z0-9_+-]+$/iD', $value) ? $value : null,
            'target' => in_array($value, ['_blank', '_self'], true) ? $value : null,
            'colwidth' => preg_match('/^\d{1,5}(,\d{1,5})*$/D', $value) ? $value : null,
            'data-align' => in_array($value, ['left', 'center', 'right'], true) ? $value : null,
            'data-display-size' => in_array($value, ContentSchema::definition()['mediaDisplaySizes'], true) ? $value : null,
            'data-type' => in_array($value, ['taskList', 'taskItem', 'details', 'detailsContent', 'detailsSummary', 'emoji', 'file'], true) ? $value : null,
            default => $value,
        };
    }
}
