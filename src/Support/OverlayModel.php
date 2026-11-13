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
        if (! str_contains($template, '<x-ui::')) {
            return $template;
        }

        $index = 0;
        $prefix = substr(hash('sha256', $template), 0, 10);

        return preg_replace_callback(
            '/<x-ui::(?:dialog|sheet|drawer|alert-dialog|popover|tooltip|hover-card|dropdown-menu(?:\.(?:sub|radio-group))?|context-menu(?:\.radio-group)?|menubar\.(?:menu|radio-group)|navigation-menu\.item|radio-group|toggle-group|tabs|accordion|collapsible|trace\.collapsible)(?=[\s\/>])(?:[^<>"\']|"[^"]*"|\'[^\']*\')*>/s',
            function (array $match) use (&$index, $prefix): string {
                $tag = $match[0];
                $markers = '';
                $index++;

                if (preg_match('/\swire:model([\w.-]*)=(["\'])(.*?)\2/s', $tag, $binding)) {
                    $markers .= ' '.self::MARKER.'="'.e('wire:model'.$binding[1].'='.$binding[3]).'"';
                } else {
                    $markers .= ' '.self::MARKER.'=""';
                }

                if (! preg_match('/^<x-ui::([^\s\/>]+)/', $tag, $root)) {
                    return $tag;
                }
                $overlay = [
                    'popover' => 'uiPopoverId', 'tooltip' => 'uiTooltipId', 'hover-card' => 'uiHoverId',
                    'dropdown-menu' => 'uiDropdownId', 'dropdown-menu.sub' => 'uiSubmenuId',
                    'context-menu' => 'uiContextId', 'menubar.menu' => 'uiMenubarId', 'navigation-menu.item' => 'uiNavigationId',
                ][$root[1]] ?? null;
                if ($overlay) {
                    $markers .= ' :'.$overlay.'="\\Esoboy\\Ui\\Support\\Model::scope(\'ui-overlay-'.$prefix.'-'.$index.'\', $loop ?? null)"';
                }

                $context = preg_match('/^<x-ui::(?:radio-group|dropdown-menu\.radio-group|context-menu\.radio-group|menubar\.radio-group|toggle-group|tabs|accordion|collapsible|trace\.collapsible)\b/', $tag)
                    ? ['value' => 'uiValue', 'open' => 'uiOpen', 'name' => 'uiName'] : [];

                foreach ($context as $attribute => $marker) {
                    if (preg_match('/\s(:?)'.preg_quote($attribute, '/').'\s*=\s*(["\'])(.*?)\2/s', $tag, $value)) {
                        $markers .= ' '.$value[1].$marker.'='.$value[2].$value[3].$value[2];
                    }
                }

                if (preg_match('/^<x-ui::(?:radio-group|dropdown-menu\.radio-group|context-menu\.radio-group|menubar\.radio-group|toggle-group)\b/', $tag) && ! str_contains($markers, 'uiName=')) {
                    $markers .= ' :uiName="\Esoboy\Ui\Support\Model::scope(\'ui-choice-'.$prefix.'-'.$index.'\', $loop ?? null)"';
                }

                return preg_replace('/\s*\/?>(?=$)/', $markers.(str_ends_with($tag, '/>') ? ' />' : '>'), $tag) ?? $tag;
            },
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
