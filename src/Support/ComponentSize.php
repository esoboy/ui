<?php

namespace Esoboy\Ui\Support;

final class ComponentSize
{
    public static function icon(string $size): string
    {
        return match ($size) {
            'xs', 'sm', 'lg' => 'icon-'.$size,
            default => 'icon',
        };
    }
}
