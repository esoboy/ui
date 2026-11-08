<?php

namespace Esoboy\Ui\Support;

/**
 * Whether the sidebar starts expanded: the sidebar_state cookie the browser writes on
 * every toggle (shadcn's), or the provider's open prop before the first toggle.
 */
final class SidebarState
{
    public const COOKIE = 'sidebar_state';

    public static function cookie(string $scope = 'default'): string
    {
        return $scope === 'default' ? self::COOKIE : self::COOKIE.'_'.substr(hash('sha256', $scope), 0, 12);
    }

    public static function open(bool $default = true, string $scope = 'default'): bool
    {
        $state = request()->cookie(self::cookie($scope));

        return is_string($state) ? $state === 'true' : $default;
    }
}
