<?php

namespace Esoboy\Ui;

use Esoboy\Ui\Support\FieldContext;
use Esoboy\Ui\Support\OverlayModel;
use Esoboy\Ui\Support\PendingControl;
use Esoboy\Ui\Support\Sequence;
use Esoboy\Ui\Support\SidebarState;
use Esoboy\Ui\Support\Theme;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ui.php', 'ui');

        $this->app->singleton(Assets::class, fn () => new Assets(__DIR__.'/../dist'));
        $this->app->singleton(UiManager::class);
        $this->app->scoped(PendingControl::class);
        $this->app->scoped(Sequence::class);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'ui');

        $this->bootConfig();
        $this->bootAssets();
        $this->bootTranslations();
        $this->bootMarkers();

        // <html @uiTheme('admin')>: the app's theme choice as data-theme; see Theme.
        Blade::directive('uiTheme', fn (string $expression) => "<?php echo \Esoboy\Ui\Support\Theme::attributes({$expression}); ?>");

        // The browser writes the sidebar and theme cookies, so they can't be encrypted.
        EncryptCookies::except([SidebarState::COOKIE, Theme::COOKIE]);
    }

    /**
     * config/ui.php: the Editor's formats, whitelist, embed platforms, hosts and uploader.
     * Its route hands what the Editor uploads to the uploader.
     */
    protected function bootConfig(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/editor.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ui.php' => config_path('ui.php'),
            ], 'ui-config');
        }
    }

    /**
     * @uiScripts loads the bundled JavaScript; the asset route serves it until it is
     * published. laravel-assets is the group new Laravel apps republish on composer update.
     */
    protected function bootAssets(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/assets.php');

        Blade::directive('uiScripts', fn (string $expression) => "<?php echo app(\Esoboy\Ui\Assets::class)->scripts({$expression}); ?>");

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/ui'),
            ], ['ui-assets', 'laravel-assets']);
        }
    }

    /**
     * Mark field tags and bound overlay roots before Blaze compiles them; see FieldContext
     * and OverlayModel. Blaze hooks the same pipeline from an app()->booted() callback, so
     * registering here runs first.
     */
    protected function bootMarkers(): void
    {
        Blade::prepareStringsForCompilationUsing(fn (string $template) => OverlayModel::mark(FieldContext::mark($template)));
    }

    /**
     * Package strings are __('ui::component.key'), one file per component in
     * lang/{locale}. Laravel merges an app's lang/vendor/ui/{locale}/{component}.php over them.
     */
    protected function bootTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'ui');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/ui'),
            ], 'ui-lang');
        }
    }
}
