<?php

namespace Esoboy\Ui;

use Esoboy\Ui\Support\Assets;
use Esoboy\Ui\View\Carousel;
use Esoboy\Ui\View\DataTable;
use Esoboy\Ui\View\DataTableCell;
use Esoboy\Ui\View\DataTableSelectionToolbar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $root = dirname(__DIR__);

        $this->loadViewsFrom($root.'/resources/views', 'ui');
        $this->loadTranslationsFrom($root.'/lang', 'ui');
        Blade::include('ui::assets', 'uiScript');
        Blade::anonymousComponentPath($root.'/resources/views/components', 'ui');

        Blade::component(Carousel::class, 'ui::carousel');
        Blade::component(DataTable::class, 'ui::data-table');
        Blade::component(DataTableCell::class, 'ui::data-table.cell');
        Blade::component(DataTableSelectionToolbar::class, 'ui::data-table.selection-toolbar');

        $this->loadRoutesFrom($root.'/routes/assets.php');
        $this->publishes([Assets::DIRECTORY => public_path(Assets::PUBLIC_DIRECTORY)], 'ui-assets');

        Livewire::addNamespace(
            namespace: 'ui',
            classNamespace: 'Esoboy\\Ui\\Livewire',
            classPath: __DIR__.'/Livewire',
            classViewPath: $root.'/resources/views/livewire',
        );
    }
}
