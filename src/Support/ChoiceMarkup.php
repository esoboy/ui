<?php

namespace Esoboy\Ui\Support;

use Dom\HTMLDocument;
use Dom\HTMLElement;
use Illuminate\View\ComponentAttributeBag;

/**
 * Keeps the custom-parts interface while rendering its selected labels in PHP.
 * Simple options use the same parts, so there is only one visual implementation.
 */
final class ChoiceMarkup
{
    private HTMLDocument $document;

    /** @var list<HTMLElement> */
    private array $items = [];

    public function __construct(string $html, private readonly string $kind = 'select')
    {
        $this->document = HtmlFragment::parse($html);

        foreach ($this->document->getElementsByTagName('*') as $element) {
            if ($element instanceof HTMLElement && $element->getAttribute('data-slot') === $kind.'-item') {
                $this->items[] = $element;
            }
        }
    }

    /** @return list<array{value: string, label: string, disabled: bool}> */
    public function options(): array
    {
        return array_map(fn (HTMLElement $item) => [
            'value' => $item->getAttribute('data-value') ?? '',
            'label' => trim(preg_replace('/\s+/u', ' ', $this->label($item)->textContent ?? '') ?? ''),
            'disabled' => $item->getAttribute('aria-disabled') === 'true',
        ], $this->items);
    }

    public function render(mixed $value, ComponentAttributeBag $attributes, bool $disabled, string $popup): string
    {
        $values = array_map('strval', is_array($value) ? $value : ($value === null ? [] : [$value]));
        $selected = [];

        foreach ($this->items as $item) {
            $chosen = in_array($item->getAttribute('data-value'), $values, true);
            $item->setAttribute('aria-selected', $chosen ? 'true' : 'false');

            if ($chosen) {
                $selected[] = $this->label($item);
            }
        }

        foreach ($this->document->getElementsByTagName('*') as $element) {
            if (! $element instanceof HTMLElement) {
                continue;
            }

            if ($element->getAttribute('data-slot') === $this->kind.'-value' && $selected !== []) {
                $element->textContent = '';

                foreach ($selected as $index => $label) {
                    if ($index > 0) {
                        $element->appendChild($this->document->createTextNode(', '));
                    }

                    foreach ($label->childNodes as $node) {
                        $element->appendChild($node->cloneNode(true));
                    }
                }
            }

            if ($element->getAttribute('data-slot') === $this->kind.'-trigger') {
                $element->setAttribute('commandfor', $popup);
                $element->setAttribute('aria-controls', $popup);
                if ($selected !== []) {
                    $element->removeAttribute('data-placeholder');
                }
                foreach ($attributes->getAttributes() as $name => $attribute) {
                    if ($attribute !== null && $attribute !== false) {
                        // Blade has escaped the value. The DOM serializer will escape it again.
                        $element->setAttribute($name, html_entity_decode((string) $attribute, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    }
                }
                if ($disabled) {
                    $element->setAttribute('disabled', '');
                }
            }

            if ($element->getAttribute('data-slot') === $this->kind.'-content') {
                $element->setAttribute('id', $popup);
            }
        }

        $body = $this->document->getElementsByTagName('body')->item(0);

        return $body ? implode('', array_map(fn ($node) => $this->document->saveHtml($node), iterator_to_array($body->childNodes))) : '';
    }

    private function label(HTMLElement $item): HTMLElement
    {
        foreach ($item->getElementsByTagName('*') as $element) {
            if ($element instanceof HTMLElement && $element->getAttribute('data-slot') === $this->kind.'-item-text') {
                return $element;
            }
        }

        return $item;
    }
}
