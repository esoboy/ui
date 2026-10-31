<?php

namespace Esoboy\Ui\Support;

/**
 * Whether the sidebar starts expanded: the sidebar_state cookie the browser writes on
 * every toggle (shadcn's), or the provider's default-open before the first toggle.
 */
final class SidebarState
{
    public const COOKIE = 'sidebar_state';

    public static function open(bool $default = true): bool
    {
        $state = request()->cookie(self::COOKIE);

        return is_string($state) ? $state === 'true' : $default;
    }
}
