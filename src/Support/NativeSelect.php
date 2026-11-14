<?php

namespace Esoboy\Ui\Support;

use Dom\HTMLDocument;

final class NativeSelect
{
    public static function render(string $options, mixed $value): string
    {
        $document = HTMLDocument::createFromString('<!doctype html><html><body><select>'.$options.'</select></body></html>', LIBXML_NOERROR);
        $values = array_map('strval', (array) $value);
        foreach ($document->getElementsByTagName('option') as $option) {
            if (in_array($option->getAttribute('value'), $values, true)) {
                $option->setAttribute('selected', 'selected');
            } else {
                $option->removeAttribute('selected');
            }
        }
        $select = $document->getElementsByTagName('select')->item(0);

        return $select ? implode('', array_map(fn ($node) => $document->saveHtml($node), iterator_to_array($select->childNodes))) : '';
    }
}
