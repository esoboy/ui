<?php

/*
 * Theme: each app's light / dark / system choice, from the ui_theme cookie.
 */

use Esoboy\Ui\Facades\Ui;
use Esoboy\Ui\Support\Theme;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

function chooseThemes(array $choices): void
{
    request()->cookies->set(Theme::COOKIE, json_encode($choices));
}

it('leaves the scheme to the system until the app has a choice', function () {
    expect(Blade::render('<html @uiTheme(\'admin\')>'))->toBe('<html data-theme-scope="admin">')
        ->and(Blade::render('<html @uiTheme>'))->toBe('<html data-theme-scope="default">')
        ->and(Ui::theme('admin'))->toBe('system');
});

it('keeps a choice per app', function () {
    chooseThemes(['admin' => 'dark', 'shop' => 'light']);

    expect(Blade::render('<html @uiTheme(\'admin\')>'))->toBe('<html data-theme-scope="admin" data-theme="dark">')
        ->and(Blade::render('<html @uiTheme(\'shop\')>'))->toBe('<html data-theme-scope="shop" data-theme="light">')
        ->and(Ui::theme('support'))->toBe('system');
});

it('ignores choices it does not know and a broken cookie', function () {
    chooseThemes(['admin' => 'blue']);
    expect(Theme::current('admin'))->toBe('system');

    request()->cookies->set(Theme::COOKIE, '{broken');
    expect(Theme::current('admin'))->toBe('system');
});

it('escapes the scope', function () {
    expect(Theme::attributes('a"b'))->toBe('data-theme-scope="a&quot;b"');
});

it('reads the cookie the browser wrote, which Laravel must not encrypt', function () {
    expect(app(EncryptCookies::class)->isDisabled(Theme::COOKIE))->toBeTrue();

    Route::middleware('web')->get('/themed', fn () => Blade::render('<html @uiTheme(\'admin\')></html>'));

    $this->withUnencryptedCookie(Theme::COOKIE, json_encode(['admin' => 'dark']))
        ->get('/themed')
        ->assertSee('<html data-theme-scope="admin" data-theme="dark">', false);
});
