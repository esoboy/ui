<?php

namespace Esoboy\Ui;

use Esoboy\Ui\Support\Assets;
use Esoboy\Ui\View\Accordion;
use Esoboy\Ui\View\AccordionItem;
use Esoboy\Ui\View\ButtonGroup;
use Esoboy\Ui\View\Carousel;
use Esoboy\Ui\View\DataTable;
use Esoboy\Ui\View\DataTableCell;
use Esoboy\Ui\View\DataTableSelectionToolbar;
use Esoboy\Ui\View\Disclosure;
use Esoboy\Ui\View\Pagination;
use Esoboy\Ui\View\Progress;
use Esoboy\Ui\View\RadioGroup;
use Esoboy\Ui\View\TabPart;
use Esoboy\Ui\View\Tabs;
use Esoboy\Ui\View\ToggleGroup;
use Esoboy\Ui\View\Tour;
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
        $compiler = $this->app->make('blade.compiler');
        $compiler->prepareStringsForCompilationUsing(static function (string $source) use ($compiler): string {
            $path = $compiler->getPath();
            $pattern = '~<x-ui::(?:select|combobox|icon-select|tags-input|mentions|calendar|date-picker|datetime-picker|time-picker|tree|tree-select|cascader|command|transfer|questionnaire|rating|slider|editor|input-otp|number-input|tabs|tour|sidebar|collapsible|trace\.collapsible|data-table|radio-group|toggle-group|accordion)(?=[\s/>])~';

            return preg_replace_callback($pattern, static function (array $tag) use ($path): string {
                [$name, $offset] = $tag[0];

                return $name.' ui-site="'.hash('xxh3', $path.':'.$offset).'"';
            }, $source, flags: PREG_OFFSET_CAPTURE) ?? $source;
        });

        Blade::component(Disclosure::class, 'ui::collapsible');
        Blade::component(Disclosure::class, 'ui::trace.collapsible');
        Blade::component(RadioGroup::class, 'ui::radio-group');
        foreach (['dropdown-menu', 'context-menu', 'menubar'] as $menu) {
            Blade::component(RadioGroup::class, 'ui::'.$menu.'.radio-group');
        }
        Blade::component(ToggleGroup::class, 'ui::toggle-group');
        Blade::component(Accordion::class, 'ui::accordion');
        Blade::component(AccordionItem::class, 'ui::accordion.item');
        Blade::component(Progress::class, 'ui::progress');
        Blade::component(ButtonGroup::class, 'ui::button-group');
        Blade::component(Tour::class, 'ui::tour');
        Blade::component(Tabs::class, 'ui::tabs');
        foreach (['tab', 'list', 'content'] as $part) {
            Blade::component(TabPart::class, 'ui::tabs.'.$part);
        }
        Blade::component(Pagination::class, 'ui::pagination');
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
