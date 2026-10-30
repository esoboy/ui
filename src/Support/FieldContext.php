<?php

namespace Esoboy\Ui\Support;

/**
 * Lets form controls know they sit inside <x-ui::field> or <x-ui::field.set>.
 *
 * Blade renders a component's slot before the component itself, so a field can't
 * announce itself at runtime. What children can see (through @aware) is the data
 * written on their ancestors' tags, so every field tag gets a marker attribute
 * before compilation.
 */
final class FieldContext
{
    public const MARKER = 'uiField';

    public static function mark(string $template): string
    {
        if (! str_contains($template, '<x-ui::field')) {
            return $template;
        }

        return preg_replace('/<x-ui::field(?:\.set)?(?=[\s\/>])/', '$0 '.self::MARKER, $template) ?? $template;
    }
}
