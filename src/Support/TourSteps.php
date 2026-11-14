<?php

namespace Esoboy\Ui\Support;

use Dom\HTMLElement;

final class TourSteps
{
    /** @return list<array{target: string, title: string, description: string, side: string, align: string}> */
    public static function read(string $slot): array
    {
        $document = HtmlFragment::parse($slot);
        $steps = [];
        foreach ($document->querySelectorAll('body > [data-tour-step]') as $element) {
            if ($element instanceof HTMLElement) {
                $steps[] = [
                    'target' => $element->getAttribute('data-target') ?? '',
                    'title' => $element->getAttribute('data-title') ?? '',
                    'side' => $element->getAttribute('data-side') ?? 'bottom',
                    'align' => $element->getAttribute('data-align') ?? 'center',
                    'description' => $element->innerHTML,
                ];
            }
        }

        return $steps;
    }
}
