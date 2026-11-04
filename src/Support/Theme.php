<?php

namespace Esoboy\Ui\Support;

/**
 * The light / dark / system choice of each app on the site (admin, shop, ...), kept in
 * one cookie the browser writes: {"admin":"dark","shop":"light"}. No entry is system.
 */
final class Theme
{
    public const COOKIE = 'ui_theme';

    private const CHOICES = ['light', 'dark', 'system'];

    /**
     * The app's choice: light, dark or system.
     */
    public static function current(string $scope = 'default'): string
    {
        $cookie = request()->cookie(self::COOKIE);
        $choices = is_string($cookie) ? json_decode($cookie, true) : null;
        $choice = is_array($choices) ? ($choices[$scope] ?? null) : null;

        return in_array($choice, self::CHOICES, true) ? $choice : 'system';
    }

    /**
     * The attributes @uiTheme puts on <html>: the scope, and the scheme when one is chosen.
     */
    public static function attributes(string $scope = 'default'): string
    {
        $choice = self::current($scope);

        return 'data-theme-scope="'.e($scope).'"'.($choice === 'system' ? '' : ' data-theme="'.$choice.'"');
    }
}
