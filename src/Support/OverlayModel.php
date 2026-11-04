<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;

/**
 * Lets Dialog, Sheet, Drawer, Alert Dialog and Popover bind whether they are open with
 * wire:model on their root, where shadcn has open: <x-ui::dialog wire:model="editing">.
 *
 * The root renders no element, and Blade renders a component's slot before the
 * component, so the content can't take the attribute from the root at runtime. What it
 * can see (through @aware) is the data written on its ancestors' tags, so the root's
 * binding is written on its tag again, as a marker, before compilation.
 */
final class OverlayModel
{
    public const MARKER = 'uiModel';

    public static function mark(string $template): string
    {
        if (! str_contains($template, 'wire:model')) {
            return $template;
        }

        return preg_replace_callback(
            '/<x-ui::(?:dialog|sheet|drawer|alert-dialog|popover)(?=[\s\/>])[^>]*?\swire:model([\w.-]*)="([^"]*)"/',
            fn (array $match) => $match[0].' '.self::MARKER.'="wire:model'.$match[1].'='.$match[2].'"',
            $template,
        ) ?? $template;
    }

    /**
     * The binding the root passed down, for the element that holds the open state.
     */
    public static function attributes(?string $marker): ?ComponentAttributeBag
    {
        if ($marker === null || ! str_contains($marker, '=')) {
            return null;
        }

        [$attribute, $property] = explode('=', $marker, 2);

        return new ComponentAttributeBag([$attribute => $property]);
    }
}
