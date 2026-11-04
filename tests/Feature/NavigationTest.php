<?php

/*
 * Breadcrumb, Table, Tabs, Pagination, Sidebar, Anchor, Float Button, Back to Top.
 */

use Esoboy\Ui\Tests\Fixtures\Invoices;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

function withCookies(array $cookies): void
{
    app()->instance('request', Request::create('/', 'GET', cookies: $cookies));
}

it('renders a breadcrumb with its parts', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::breadcrumb>
            <x-ui::breadcrumb.list>
                <x-ui::breadcrumb.item><x-ui::breadcrumb.link href="/">Home</x-ui::breadcrumb.link></x-ui::breadcrumb.item>
                <x-ui::breadcrumb.separator />
                <x-ui::breadcrumb.item><x-ui::breadcrumb.ellipsis /></x-ui::breadcrumb.item>
                <x-ui::breadcrumb.separator>/</x-ui::breadcrumb.separator>
                <x-ui::breadcrumb.item><x-ui::breadcrumb.page>Settings</x-ui::breadcrumb.page></x-ui::breadcrumb.item>
            </x-ui::breadcrumb.list>
        </x-ui::breadcrumb>
        BLADE);

    $view->assertSeeInOrder([
        '<nav data-slot="breadcrumb" aria-label="breadcrumb">',
        '<ol data-slot="breadcrumb-list">',
        '<li data-slot="breadcrumb-item"><a data-slot="breadcrumb-link" href="/">Home</a></li>',
        '<li data-slot="breadcrumb-separator" role="presentation" aria-hidden="true">',
        '<path d="m9 18 6-6-6-6"/>',
        '<span data-slot="breadcrumb-ellipsis" role="presentation" aria-hidden="true">',
        '<span class="sr-only">More</span>',
        '/',
        '<span data-slot="breadcrumb-page" role="link" aria-disabled="true" aria-current="page">Settings</span>',
    ], false);
});

it('renders a table in its scroll container', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::table>
            <x-ui::table.caption>Invoices</x-ui::table.caption>
            <x-ui::table.header><x-ui::table.row><x-ui::table.head>Invoice</x-ui::table.head></x-ui::table.row></x-ui::table.header>
            <x-ui::table.body><x-ui::table.row data-state="selected"><x-ui::table.cell>INV001</x-ui::table.cell></x-ui::table.row></x-ui::table.body>
            <x-ui::table.footer><x-ui::table.row><x-ui::table.cell>Total</x-ui::table.cell></x-ui::table.row></x-ui::table.footer>
        </x-ui::table>
        BLADE);

    $view->assertSeeInOrder([
        '<div data-slot="table-container">',
        '<table data-slot="table">',
        '<caption data-slot="table-caption">Invoices</caption>',
        '<thead data-slot="table-header"><tr data-slot="table-row"><th data-slot="table-head">Invoice</th></tr></thead>',
        '<tbody data-slot="table-body"><tr data-slot="table-row" data-state="selected"><td data-slot="table-cell">INV001</td></tr></tbody>',
        '<tfoot data-slot="table-footer">',
    ], false);
});

describe('tabs', function () {
    it('paints the default tab on the server', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::tabs default-value="password">
                <x-ui::tabs.list variant="line">
                    <x-ui::tabs.trigger value="account">Account</x-ui::tabs.trigger>
                    <x-ui::tabs.trigger value="password">Password</x-ui::tabs.trigger>
                </x-ui::tabs.list>
                <x-ui::tabs.content value="account">A</x-ui::tabs.content>
                <x-ui::tabs.content value="password">P</x-ui::tabs.content>
            </x-ui::tabs>
            BLADE);

        $view->assertSee('<eso-tabs data-slot="tabs" data-orientation="horizontal" data-value="password">', false)
            ->assertSee('<div role="tablist" data-slot="tabs-list" data-variant="line" aria-orientation="horizontal">', false)
            ->assertSee('<button type="button" role="tab" wire:key="tabs-trigger-account" data-slot="tabs-trigger" data-value="account" aria-selected="false" tabindex="-1">Account</button>', false)
            ->assertSee('<button type="button" role="tab" wire:key="tabs-trigger-password" data-slot="tabs-trigger" data-value="password" data-active="data-active" aria-selected="true" tabindex="0">Password</button>', false)
            ->assertSee('<div role="tabpanel" wire:key="tabs-content-account" tabindex="0" data-slot="tabs-content" data-value="account" hidden="hidden">A</div>', false)
            ->assertSee('<div role="tabpanel" wire:key="tabs-content-password" tabindex="0" data-slot="tabs-content" data-value="password">P</div>', false);
    });

    it('passes the orientation to the list', function () {
        $this->blade('<x-ui::tabs orientation="vertical"><x-ui::tabs.list /></x-ui::tabs>')
            ->assertSee('data-orientation="vertical"', false)
            ->assertSee('aria-orientation="vertical"', false);
    });

    it('starts from the bound Livewire value', function () {
        expect(Livewire::test(Invoices::class)->html())
            ->toContain('<eso-tabs data-slot="tabs" data-orientation="horizontal" data-value="account" wire:model="tab">');
    });
});

describe('pagination', function () {
    it('composes links like shadcn', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::pagination>
                <x-ui::pagination.content>
                    <x-ui::pagination.item><x-ui::pagination.previous href="?page=1" /></x-ui::pagination.item>
                    <x-ui::pagination.item><x-ui::pagination.link href="?page=2" is-active>2</x-ui::pagination.link></x-ui::pagination.item>
                    <x-ui::pagination.item><x-ui::pagination.ellipsis /></x-ui::pagination.item>
                    <x-ui::pagination.item><x-ui::pagination.next href="?page=3" text="Onward" /></x-ui::pagination.item>
                </x-ui::pagination.content>
            </x-ui::pagination>
            BLADE);

        $view->assertSeeInOrder([
            '<nav role="navigation" aria-label="pagination" data-slot="pagination">',
            '<ul data-slot="pagination-content">',
            '<a data-slot="button" data-variant="ghost" data-size="default" aria-label="Go to previous page" href="?page=1">',
            '<span class="hidden sm:block">Previous</span>',
            '<a data-slot="button" data-variant="outline" data-size="icon" aria-current="page" data-active="data-active" href="?page=2">2</a>',
            '<span class="sr-only">More pages</span>',
            '<span class="hidden sm:block">Onward</span>',
        ], false);
    });

    it('renders a paginator as hrefs, with the current page and ellipses', function () {
        $paginator = new LengthAwarePaginator(range(1, 10), 200, 10, 10, ['path' => '/invoices']);
        $html = (string) $this->blade('<x-ui::pagination :paginator="$paginator" />', ['paginator' => $paginator]);

        expect(idsOf($html, 'href'))->toBe([
            '/invoices?page=9',
            '/invoices?page=1', '/invoices?page=2',
            '/invoices?page=7', '/invoices?page=8', '/invoices?page=9', '/invoices?page=10', '/invoices?page=11', '/invoices?page=12', '/invoices?page=13',
            '/invoices?page=19', '/invoices?page=20',
            '/invoices?page=11',
        ])
            ->and(substr_count($html, 'data-slot="pagination-ellipsis"'))->toBe(2)
            ->and($html)->toContain('aria-current="page" data-active="data-active" href="/invoices?page=10">10</a>');
    });

    it('disables previous on the first page and next on the last', function () {
        $first = (string) $this->blade('<x-ui::pagination :paginator="$p" />', ['p' => new LengthAwarePaginator([1], 2, 1, 1)]);
        $last = (string) $this->blade('<x-ui::pagination :paginator="$p" />', ['p' => new LengthAwarePaginator([1], 2, 1, 2)]);

        expect($first)->toMatch('/<button[^>]*aria-label="Go to previous page"[^>]*disabled/')
            ->and($last)->toMatch('/<button[^>]*aria-label="Go to next page"[^>]*disabled/');
    });

    it('renders only previous and next for simple and cursor paginators', function () {
        $simple = (string) $this->blade('<x-ui::pagination :paginator="$p" />', ['p' => new Paginator(range(1, 6), 5, 2, ['path' => '/list'])]);
        $cursor = (string) $this->blade('<x-ui::pagination :paginator="$p" />', ['p' => new CursorPaginator([], 5, null, ['path' => '/list'])]);

        expect(idsOf($simple, 'href'))->toBe(['/list?page=1', '/list?page=3'])
            ->and($simple)->not->toContain('data-size="icon"')
            ->and($cursor)->toBe('');
    });

    it('renders nothing for a single page', function () {
        expect((string) $this->blade('<x-ui::pagination :paginator="$p" />', ['p' => new LengthAwarePaginator([1], 1, 10)]))->toBe('');
    });

    it('calls the Livewire page methods inside a component', function () {
        $html = Livewire::test(Invoices::class)->call('setPage', 4)->html();

        expect($html)->toContain('aria-label="Go to previous page" wire:click="setPage(3, \'page\')"')
            ->toContain('aria-label="Go to next page" wire:click="setPage(5, \'page\')"')
            ->toContain('aria-current="page" data-active="data-active" wire:click="setPage(4, \'page\')"')
            ->not->toContain('href=');
    });
});

describe('sidebar', function () {
    it('renders the desktop structure, expanded by default', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::sidebar.provider>
                <x-ui::sidebar collapsible="icon" class="border-0">
                    <x-ui::sidebar.content>Nav</x-ui::sidebar.content>
                </x-ui::sidebar>
                <x-ui::sidebar.inset><x-ui::sidebar.trigger /></x-ui::sidebar.inset>
            </x-ui::sidebar.provider>
            BLADE);

        $view->assertSeeInOrder([
            '<eso-sidebar-provider data-slot="sidebar-wrapper" style="--sidebar-width: 16rem; --sidebar-width-icon: 3rem;">',
            '<div data-slot="sidebar" data-state="expanded" data-collapsible="" data-collapsible-mode="icon" data-variant="sidebar" data-side="left">',
            '<div data-slot="sidebar-gap"></div>',
            '<div data-slot="sidebar-container" data-side="left" popover="manual" class="border-0">',
            '<div data-sidebar="sidebar" data-slot="sidebar-inner">',
            '<div data-slot="sidebar-content" data-sidebar="content">Nav</div>',
            '<main data-slot="sidebar-inset">',
            'data-sidebar="trigger"',
            '<span class="sr-only">Toggle Sidebar</span>',
        ], false);
    });

    it('starts collapsed from the provider or the sidebar_state cookie', function () {
        $template = '<x-ui::sidebar.provider :default-open="$open"><x-ui::sidebar collapsible="offcanvas" /></x-ui::sidebar.provider>';

        $this->blade($template, ['open' => false])->assertSee('data-state="collapsed" data-collapsible="offcanvas"', false);

        withCookies(['sidebar_state' => 'true']);
        $this->blade($template, ['open' => false])->assertSee('data-state="expanded" data-collapsible=""', false);

        withCookies(['sidebar_state' => 'false']);
        $this->blade($template, ['open' => true])->assertSee('data-state="collapsed"', false);
    });

    it('leaves the sidebar_state cookie unencrypted, since the browser writes it', function () {
        expect(app(EncryptCookies::class)->isDisabled('sidebar_state'))->toBeTrue();
    });

    it('renders a plain column when not collapsible', function () {
        $this->blade('<x-ui::sidebar collapsible="none">Nav</x-ui::sidebar>')
            ->assertSee('<div data-slot="sidebar">Nav</div>', false);
    });

    it('renders menu buttons as links or buttons with a collapsed-only tooltip', function () {
        $html = (string) $this->blade(<<<'BLADE'
            <x-ui::sidebar.menu>
                <x-ui::sidebar.menu-item>
                    <x-ui::sidebar.menu-button href="/" is-active tooltip="Home"><span>Home</span></x-ui::sidebar.menu-button>
                    <x-ui::sidebar.menu-action show-on-hover>…</x-ui::sidebar.menu-action>
                    <x-ui::sidebar.menu-badge>3</x-ui::sidebar.menu-badge>
                </x-ui::sidebar.menu-item>
                <x-ui::sidebar.menu-item>
                    <x-ui::sidebar.menu-button size="lg" variant="outline">Team</x-ui::sidebar.menu-button>
                    <x-ui::sidebar.menu-sub>
                        <x-ui::sidebar.menu-sub-item><x-ui::sidebar.menu-sub-button href="/a" size="sm" is-active>A</x-ui::sidebar.menu-sub-button></x-ui::sidebar.menu-sub-item>
                    </x-ui::sidebar.menu-sub>
                </x-ui::sidebar.menu-item>
            </x-ui::sidebar.menu>
            BLADE);

        expect($html)
            ->toContain('<a data-slot="sidebar-menu-button" data-sidebar="menu-button" data-variant="default" data-size="default" data-active="data-active" data-tooltip-trigger="data-tooltip-trigger" href="/"><span>Home</span></a>')
            ->toContain('<div popover="manual" role="tooltip" data-slot="tooltip-content" data-side="right"')
            ->toContain('data-sidebar="menu-tooltip"')
            ->toContain('<button type="button" data-slot="sidebar-menu-action" data-sidebar="menu-action" data-show-on-hover="data-show-on-hover">')
            ->toContain('<div data-slot="sidebar-menu-badge" data-sidebar="menu-badge">3</div>')
            ->toContain('<button type="button" data-slot="sidebar-menu-button" data-sidebar="menu-button" data-variant="outline" data-size="lg">Team</button>')
            ->toContain('<a data-slot="sidebar-menu-sub-button" data-sidebar="menu-sub-button" data-size="sm" data-active="data-active" href="/a">A</a>');
    });

    it('renders a rail, input, separator and skeleton', function () {
        $html = (string) $this->blade(<<<'BLADE'
            <x-ui::sidebar.rail />
            <x-ui::sidebar.input placeholder="Search" />
            <x-ui::sidebar.separator />
            <x-ui::sidebar.menu-skeleton show-icon />
            BLADE);

        expect($html)
            ->toContain('<button type="button" data-sidebar="rail" data-slot="sidebar-rail" aria-label="Toggle Sidebar" title="Toggle Sidebar" tabindex="-1"></button>')
            ->toContain('data-sidebar="input"')
            ->toContain('data-sidebar="separator"')
            ->toContain('data-sidebar="menu-skeleton-icon"')
            ->toMatch('/--skeleton-width: (5\d|[6-8]\d)%/');
    });
});

it('renders an anchor list of the page\'s sections', function () {
    $this->blade(<<<'BLADE'
        <x-ui::anchor>
            <x-ui::anchor.title>On This Page</x-ui::anchor.title>
            <x-ui::anchor.item href="#install">Install</x-ui::anchor.item>
            <x-ui::anchor.item href="#props" :level="2">Props</x-ui::anchor.item>
        </x-ui::anchor>
        BLADE)
        ->assertSeeInOrder([
            '<eso-anchor role="navigation" aria-label="On this page" data-slot="anchor">',
            '<p data-slot="anchor-title">On This Page</p>',
            '<a data-slot="anchor-item" data-level="1" href="#install">Install</a>',
            '<a data-slot="anchor-item" data-level="2" href="#props">Props</a>',
        ], false);
});

it('renders float buttons and a back to top button that starts hidden', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::float-button.group>
            <x-ui::float-button aria-label="Chat">C</x-ui::float-button>
            <x-ui::back-to-top target="#main" :threshold="200" />
        </x-ui::float-button.group>
        BLADE);

    expect($html)
        ->toContain('<div data-slot="float-button-group">')
        ->toMatch('/<button[^>]*data-variant="outline" data-size="icon-lg" aria-label="Chat">C<\/button>/')
        ->toContain('<eso-back-to-top data-slot="back-to-top"  data-target="#main"  data-threshold="200" hidden wire:ignore.self>')
        ->toMatch('/data-size="icon-lg" aria-label="Back to top"><svg/');
});
