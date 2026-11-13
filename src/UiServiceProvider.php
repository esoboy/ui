<?php

namespace Esoboy\Ui;

use Esoboy\Ui\Livewire\Calendar;
use Esoboy\Ui\Livewire\Cascader;
use Esoboy\Ui\Livewire\ColorPicker;
use Esoboy\Ui\Livewire\Combobox;
use Esoboy\Ui\Livewire\Command;
use Esoboy\Ui\Livewire\Confirm;
use Esoboy\Ui\Livewire\Countdown;
use Esoboy\Ui\Livewire\DatePicker;
use Esoboy\Ui\Livewire\EditorPanels;
use Esoboy\Ui\Livewire\EmojiPicker;
use Esoboy\Ui\Livewire\EmojiPreview;
use Esoboy\Ui\Livewire\ImagePreview;
use Esoboy\Ui\Livewire\InputCount;
use Esoboy\Ui\Livewire\InputOtp;
use Esoboy\Ui\Livewire\MentionsResults;
use Esoboy\Ui\Livewire\NumberInput;
use Esoboy\Ui\Livewire\PendingUpload;
use Esoboy\Ui\Livewire\Questionnaire;
use Esoboy\Ui\Livewire\Rating;
use Esoboy\Ui\Livewire\Select;
use Esoboy\Ui\Livewire\TagsInput;
use Esoboy\Ui\Livewire\TimePicker;
use Esoboy\Ui\Livewire\Toaster;
use Esoboy\Ui\Livewire\Tour;
use Esoboy\Ui\Livewire\Transfer;
use Esoboy\Ui\Livewire\Tree;
use Esoboy\Ui\Livewire\TreeSelect;
use Esoboy\Ui\Support\FieldContext;
use Esoboy\Ui\Support\OverlayModel;
use Esoboy\Ui\Support\PendingControl;
use Esoboy\Ui\Support\Sequence;
use Esoboy\Ui\Support\SidebarState;
use Esoboy\Ui\Support\Theme;
use Esoboy\Ui\Support\ViewSlots;
use Esoboy\Ui\View\DataTable;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        ViewSlots::boot();
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'ui');
        Blade::component(DataTable::class, 'ui::data-table.table');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ui');
        Livewire::listen('render', fn ($component) => Sequence::resetPrefix('scope:'.$component->getId().':'));
        Livewire::component('ui.calendar', Calendar::class);
        Livewire::component('ui.date-picker', DatePicker::class);
        Livewire::component('ui.time-picker', TimePicker::class);
        Livewire::component('ui.tree', Tree::class);
        Livewire::component('ui.tree-select', TreeSelect::class);
        Livewire::component('ui.transfer', Transfer::class);
        Livewire::component('ui.tags-input', TagsInput::class);
        Livewire::component('ui.cascader', Cascader::class);
        Livewire::component('ui.combobox', Combobox::class);
        Livewire::component('ui.select', Select::class);
        Livewire::component('ui.number-input', NumberInput::class);
        Livewire::component('ui.rating', Rating::class);
        Livewire::component('ui.input-otp', InputOtp::class);
        Livewire::component('ui.input-count', InputCount::class);
        Livewire::component('ui.command', Command::class);
        Livewire::component('ui.mentions-results', MentionsResults::class);
        Livewire::component('ui.pending-upload', PendingUpload::class);
        Livewire::component('ui.toaster', Toaster::class);
        Livewire::component('ui.confirm', Confirm::class);
        Livewire::component('ui.countdown', Countdown::class);
        Livewire::component('ui.color-picker', ColorPicker::class);
        Livewire::component('ui.image-preview', ImagePreview::class);
        Livewire::component('ui.editor-panels', EditorPanels::class);
        Livewire::component('ui.tour', Tour::class);
        Livewire::component('ui.questionnaire', Questionnaire::class);
        Livewire::component('ui.emoji-picker', EmojiPicker::class);
        Livewire::component('ui.emoji-preview', EmojiPreview::class);

        $this->bootConfig();
        $this->bootAssets();
        $this->bootTranslations();
        $this->bootMarkers();

        // <html @uiTheme('admin')>: the app's theme choice as data-theme; see Theme.
        Blade::directive('uiTheme', fn (string $expression) => "<?php echo \Esoboy\Ui\Support\Theme::attributes({$expression}); ?>");

        // The browser writes the sidebar and theme cookies, so they can't be encrypted.
        EncryptCookies::except([SidebarState::COOKIE, SidebarState::COOKIE.'_*', Theme::COOKIE]);
    }

    /**
     * config/ui.php: the Editor's formats, whitelist and embed platforms.
     */
    protected function bootConfig(): void
    {
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
