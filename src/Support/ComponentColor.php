<?php

namespace Esoboy\Ui\Support;

use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;

final class ComponentColor
{
    public static function attributes(?string $color, string $scope, ?ComponentAttributeBag $attributes = null): ComponentAttributeBag
    {
        $attributes ??= new ComponentAttributeBag;
        $color = trim($color ?? '');
        if ($color === '') {
            return $attributes;
        }

        if (preg_match('~\A([a-z][\w-]*)(?:/(100(?:\.0+)?|\d{1,2}(?:\.\d+)?))?\z~i', $color, $palette)) {
            $name = $palette[1];
            $fallback = match (strtolower($name)) {
                'current', 'inherit' => 'currentColor',
                default => ctype_alpha($name) ? $name : 'currentColor',
            };
            $attributes = $attributes->merge(['data-ui-palette' => $name]);
            $color = "var(--ui-palette-color, var(--color-$name, $fallback))";
            if (isset($palette[2])) {
                $color = "color-mix(in oklab, $color {$palette[2]}%, transparent)";
            }
        } elseif (! self::isCssColor($color)) {
            throw new InvalidArgumentException(__('ui::errors.component_color'));
        }

        return $attributes->style('--ui-color: '.$color)->merge(['data-ui-color' => $scope]);
    }

    private static function isCssColor(string $color): bool
    {
        // Accept one color expression, including nested color functions and variable fallbacks.
        $literal = '\#(?:[\da-f]{3,4}|[\da-f]{6}|[\da-f]{8})';
        $function = '(?:rgba?|hsla?|hwb|(?:ok)?lab|(?:ok)?lch|color|color-mix|light-dark|var|calc|clamp|min|max)';
        $token = '(?:[\d#.,%/+*\s-]++|[a-z_][\w-]*+(?!\())';

        return (bool) preg_match("~\\A(?:$literal|(?<fn>$function\\((?:$token|(?&fn))*\\)))\\z~i", $color);
    }
}
