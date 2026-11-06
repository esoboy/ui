<?php

it('renders default cells and caller-owned row slots through the composed table', function () {
    $this->blade(<<<'BLADE'
        <x-ui::data-table>
            <x-ui::data-table.table
                :rows="[['id' => 9, 'name' => 'Ada', 'status' => 'raw']]"
                :columns="['name' => ['title' => 'Name'], 'status' => ['title' => 'Status']]"
                actions
            >
                @foreach ($component->entries() as $entry)
                    <x-ui::data-table.row :entry="$entry">
                        <x-slot:status>Custom status</x-slot:status>
                        <x-slot:actions><button wire:click="edit({{ $entry->row['id'] }})">Edit</button></x-slot:actions>
                    </x-ui::data-table.row>
                @endforeach
            </x-ui::data-table.table>
        </x-ui::data-table>
        BLADE)
        ->assertSeeInOrder(['Name', 'Status', 'Ada', 'Custom status', 'Edit'])
        ->assertSeeHtml('wire:click="edit(9)"')
        ->assertDontSee('raw');
});

/*
 * Data Table: the WithDataTable state and the parts that read it.
 */

use Esoboy\Ui\Tests\Fixtures\Payments;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

it('restores the page query default from the sort select', function () {
    Livewire::test(Payments::class)
        ->call('sort', 'email', 'desc')
        ->call('setPage', 3)
        ->call('sort', 'email', 'default')
        ->assertSet('sortBy', null)
        ->assertSet('paginators.page', 1);
});

it('clears just the selection associated with the toolbar', function () {
    Livewire::test(Payments::class)
        ->set('selected', [1])
        ->set('independentSelection', [2])
        ->call('clearSelection', 'selected')
        ->assertSet('selected', [])
        ->assertSet('independentSelection', [2]);
});

it('sorts a column ascending, then descending, back on the first page', function () {
    Livewire::test(Payments::class)
        ->call('setPage', 3)
        ->call('sort', 'email', 'asc')
        ->assertSet('sortBy', 'email')
        ->assertSet('sortDirection', 'asc')
        ->assertSet('paginators.page', 1)
        ->call('sort', 'email', 'desc')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'email', 'asc')
        ->assertSet('sortDirection', 'asc');
});

it('applies only allowed sorts to the query', function () {
    $component = Livewire::test(Payments::class);

    expect($component->instance()->sortedSql())->not->toContain('order by');

    $component->call('sort', 'email', 'desc');
    expect($component->instance()->sortedSql())->toContain('order by "email" desc');

    $component->call('sort', 'password', 'asc');
    expect($component->instance()->sortedSql())->not->toContain('order by');
});

it('hides and shows columns', function () {
    Livewire::test(Payments::class)
        ->call('toggleColumn', 'email')
        ->assertSet('hiddenColumns', ['email'])
        ->assertSee('data-slot="dropdown-menu-checkbox-item"', false)
        ->call('toggleColumn', 'email')
        ->assertSet('hiddenColumns', []);
});

it('renders one header button and marks the active direction in its menu', function () {
    $html = Livewire::test(Payments::class)->call('sort', 'email', 'desc')->html();

    expect($html)
        ->toContainMarkup('<th aria-sort="descending">')
        ->toContainMarkup('<button data-slot="button" data-data-table-sort aria-label="Email — Sort" aria-haspopup="menu">')
        ->toContainMarkup('<button role="menuitemradio" aria-checked="true" data-data-table-sort-option="desc">')
        ->toContainMarkup('<button role="menuitemradio" aria-checked="false" data-data-table-sort-option="default">');
});

it('renders the selection boxes bound to the selected rows', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toContainMarkup('<eso-data-table data-slot="data-table">')
        ->toMatch('/data-data-table-select-all="data-data-table-select-all" aria-label="Select all"/')
        ->toMatch('/wire:model.live="selected" value="1" data-data-table-select-row="data-data-table-select-row" aria-label="Select row"/')
        ->not->toContain('No results.');
});

it('renders the selection summary from its Data Table', function (string $locale, string $summary) {
    app()->setLocale($locale);

    Livewire::test(Payments::class)
        ->assertDontSee('Payment actions')
        ->set('selected', [1, 2])
        ->assertSee($summary)
        ->assertSee('Payment actions');
})->with([
    ['en', '2 selected'],
    ['zh_CN', '已选 2 项'],
]);

it('renders distinct filter slots with opt-in controls owned by the page', function () {
    $this->blade(<<<'BLADE'
        <x-ui::data-table.toolbar collapsible clear-filters clear-action="clearUserFilters">
            <x-slot:primary>Primary filters</x-slot:primary>
            <x-slot:secondary>Secondary filters</x-slot:secondary>
        </x-ui::data-table.toolbar>
        BLADE)
        ->assertSeeInOrder(['Primary filters', 'Secondary filters'])
        ->assertSeeHtml('wire:click="clearUserFilters"')
        ->assertSeeHtml('aria-expanded="false"');
});

it('renders a detail and keyed summary under the same public column layout', function () {
    $this->blade(<<<'BLADE'
        <x-ui::data-table.table
            :rows="[['id' => 9, 'name' => 'Ada', 'amount' => 12]]"
            :columns="['amount' => ['title' => 'Amount'], 'name' => ['title' => 'Name', 'fixed' => 'start']]"
            details
        >
            @foreach ($component->entries() as $entry)
                <x-ui::data-table.row :entry="$entry">
                    <x-slot:detail>Detail {{ $entry->row['id'] }}</x-slot:detail>
                </x-ui::data-table.row>
            @endforeach
            <x-slot:summary>
                <x-ui::data-table.summary-row :values="['amount' => 12, 'name' => 'Subtotal']" />
            </x-slot:summary>
        </x-ui::data-table.table>
        BLADE)
        ->assertSeeInOrder(['Name', 'Amount', 'Ada', 'Detail 9', 'Subtotal', '12'])
        ->assertSeeMarkup('<td colspan="3" data-slot="table-cell">')
        ->assertSeeMarkup('<th scope="col" data-sticky="start">');
});

it('renders nested rows from the configured children field with separate toggles', function () {
    $this->blade(<<<'BLADE'
        <x-ui::data-table.table
            :rows="[['id' => 'root', 'name' => 'Root', 'items' => [['id' => 'leaf', 'name' => 'Leaf']]]]"
            :columns="['name' => ['title' => 'Name']]"
            tree
            children-field="items"
            details
        >
            @foreach ($component->entries() as $entry)
                <x-ui::data-table.row :entry="$entry">
                    <x-slot:detail>Details for {{ $entry->row['name'] }}</x-slot:detail>
                </x-ui::data-table.row>
            @endforeach
        </x-ui::data-table.table>
        BLADE)
        ->assertSeeMarkup('<tr data-row-id="leaf" data-parent="root" hidden>')
        ->assertSeeHtml('data-data-table-row-toggle="tree"')
        ->assertSeeHtml('data-data-table-row-toggle="detail"')
        ->assertSee('Details for Leaf');
});

it('adds and removes only the submitted row IDs in registered selection fields', function () {
    Livewire::test(Payments::class)
        ->set('selected', [99, 2])
        ->call('selectRows', 'selected', [1, '2', 1], true)
        ->assertSetStrict('selected', ['99', '2', '1'])
        ->call('selectRows', 'selected', [1, 2], false)
        ->assertSetStrict('selected', ['99'])
        ->call('selectRows', 'hiddenColumns', ['email'], true)
        ->assertSet('hiddenColumns', []);
});

it('clears selection when the result criteria change and preserves independent selection', function (string $operation, array $arguments) {
    $table = Livewire::test(Payments::class)
        ->set('filters.status', ['pending'])
        ->set('selected', [1, 2])
        ->set('independentSelection', [99]);

    $table->{$operation}(...$arguments)
        ->assertSet('selected', [])
        ->assertSet('independentSelection', [99]);
})->with([
    'search' => ['set', ['search', 'ada']],
    'filter binding' => ['set', ['filters.status', ['paid']]],
    'facet toggle' => ['call', ['toggleFilter', 'status', 'paid']],
    'clear column' => ['call', ['clearFilters', 'status']],
    'clear all filters' => ['call', ['clearFilters']],
    'reset search and filters' => ['call', ['resetFilters']],
]);

it('preserves selection when changing pagination, order or page size', function () {
    Livewire::test(Payments::class)
        ->set('selected', [1, 2])
        ->call('setPage', 2)->assertSet('selected', [1, 2])
        ->call('sort', 'email', 'asc')->assertSet('selected', [1, 2])
        ->set('perPage', 20)->assertSet('selected', [1, 2]);
});

it('renders the total, page size and numbered Livewire pagination', function () {
    $html = Livewire::test(Payments::class)->set('selected', [1, 2])->call('setPage', 2)->html();

    expect($html)
        ->toContainMarkup('<p data-slot="data-table-count" aria-live="polite">42 total</p>')
        ->toContainMarkup('<span>Rows per page</span>')
        ->toContain('wire:name="ui.select"')
        ->toMatch('/<button[^>]*aria-current="page"[^>]*>\s*2\s*<\/button>/')
        ->toContain('wire:click="setPage(1, \'page\')"')
        ->toContain('wire:click="setPage(5, \'page\')"');
});

it('uses links and ellipses for a large paginator outside Livewire', function () {
    $paginator = new LengthAwarePaginator([], 500, 10, 20, ['pageName' => 'payments']);

    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::data-table selectable>
            <x-ui::data-table.pagination :paginator="$paginator" />
        </x-ui::data-table>
        BLADE, compact('paginator'));

    expect($html)
        ->toContain('data-slot="pagination-ellipsis"')
        ->toContain('href="/?payments=1"', 'href="/?payments=50"')
        ->toMatch('/<a[^>]*aria-current="page"[^>]*>20<\/a>/');
});

it('renders simple paginator links independently of a total', function () {
    $paginator = new Paginator([1, 2, 3], 2);

    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::data-table selectable>
            <x-ui::data-table.pagination :paginator="$paginator" />
        </x-ui::data-table>
        BLADE, compact('paginator'));

    expect($html)
        ->toContain('href="/?page=2"')
        ->not->toContain('2 total');
});

it('resets to the first page when the page size changes', function () {
    Livewire::test(Payments::class)
        ->call('setPage', 4)
        ->set('perPage', 20)
        ->assertSet('paginators.page', 1);
});

it('resets the configured paginator and preserves other paginators', function () {
    $table = Livewire::test(Payments::class)
        ->set('pageName', 'payments')
        ->call('setPage', 4)
        ->call('setPage', 3, 'payments')
        ->assertSeeHtml("setPage(2, 'payments')");

    $table->call('sort', 'email', 'asc')->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->call('toggleFilter', 'status', 'paid')->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->call('clearFilters', 'status')->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->call('resetFilters')->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->set('search', 'ada')->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->set('filters.status', ['paid'])->assertSet('paginators.payments', 1)
        ->call('setPage', 3, 'payments')->set('perPage', 20)->assertSet('paginators.payments', 1)
        ->assertSet('paginators.page', 4);
});

it('takes only the page sizes the pager offers, from the URL or the browser', function () {
    expect(Livewire::test(Payments::class)->html())
        ->toContain('data-value="25"')
        ->toContain('data-value="15"')
        ->toContain('data-value="50"');

    Livewire::withQueryParams(['perPage' => 1000000])
        ->test(Payments::class)
        ->assertSet('perPage', 10)
        ->set('perPage', 30)
        ->assertSet('perPage', 30)
        ->set('perPage', 15)
        ->assertSet('perPage', 15)
        ->set('perPage', 7)
        ->assertSet('perPage', 10);
});

it('resets the page on search alongside the app\'s own updatedSearch()', function () {
    Livewire::test(Payments::class)
        ->call('setPage', 3)
        ->set('search', 'ada')
        ->assertSet('searchedFor', 'ada')
        ->assertSet('paginators.page', 1);
});

it('keeps only filters that are lists of values', function () {
    Livewire::withQueryParams(['filters' => ['status' => [['paid']], 'method' => ['card', ['x']], 0 => ['y']]])
        ->test(Payments::class)
        ->assertSet('filters', ['method' => ['card']])
        ->set('filters.status', [['failed']])
        ->assertSet('filters', ['method' => ['card']]);
});

it('filters columns, back on the first page, and resets filters and search', function () {
    $component = Livewire::test(Payments::class)
        ->call('setPage', 3)
        ->call('toggleFilter', 'status', 'paid')
        ->assertSet('filters', ['status' => ['paid']])
        ->assertSet('paginators.page', 1)
        ->call('toggleFilter', 'status', 'failed')
        ->assertSet('filters', ['status' => ['paid', 'failed']])
        ->call('toggleFilter', 'status', 'paid')
        ->call('toggleFilter', 'status', 'failed')
        ->assertSet('filters', []);

    expect($component->instance()->filtered())->toBeFalse();

    $component->call('toggleFilter', 'method', 'card')
        ->call('toggleFilter', 'status', 'paid')
        ->call('clearFilters', 'method')
        ->assertSet('filters', ['status' => ['paid']])
        ->set('search', 'ada')
        ->call('resetFilters')
        ->assertSet('filters', [])
        ->assertSet('search', '');
});

it('applies only filterable columns, and counts each facet under the other filters', function () {
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->string('method');
    });

    DB::table('payments')->insert([
        ['status' => 'paid', 'method' => 'card'],
        ['status' => 'paid', 'method' => 'bank'],
        ['status' => 'failed', 'method' => 'card'],
        ['status' => 'pending', 'method' => 'card'],
    ]);

    $component = Livewire::test(Payments::class)
        ->call('toggleFilter', 'status', 'paid')
        ->call('toggleFilter', 'status', 'failed')
        ->call('toggleFilter', 'method', 'card')
        ->set('filters.email', ['x']);

    expect($component->instance()->filteredIds())->toBe([1, 3])
        ->and($component->instance()->statusCounts())->toBe(['failed' => 1, 'paid' => 1, 'pending' => 1]);
});

it('renders the caller-provided faceted filter and clear control', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toMatch('/data-variant="outline" data-size="sm"[^>]*data-data-table-faceted-filter="data-data-table-faceted-filter"/')
        ->toContainMarkup('<div data-value="paid" wire:click="$parent.toggleFilter(\'status\', \'paid\')">')
        ->toContainMarkup('<span data-slot="data-table-filter-count">3</span>')
        ->not->toContain('data-chosen')
        ->not->toContain('data-data-table-reset');

    $html = Livewire::test(Payments::class)->call('toggleFilter', 'status', 'paid')->html();

    expect($html)
        ->toContainMarkup('<span data-data-table-filter-values>')
        ->toMatch('/data-data-table-filter-values[^>]*>.*?data-slot="badge"[^>]*>\s*(?:<!--.*?-->\s*)*Paid/s')
        ->toContainMarkup('<button wire:click="resetFilters" data-data-table-reset>');
});

it('shows a default empty row when the page query has no records', function () {
    $this->blade('<x-ui::data-table.table :rows="[]" :columns="[\'name\' => [\'title\' => \'Name\']]" />')
        ->assertSee('No results.')
        ->assertSeeMarkup('<td data-data-table-empty colspan="1">')
        ->assertDontSeeHtml('data-data-table-select-all');
});

it('fixes columns and the header', function () {
    $this->blade('<x-ui::data-table.content sticky-header><x-ui::table.row><x-ui::table.head sticky="start">A</x-ui::table.head><x-ui::table.cell sticky="end">B</x-ui::table.cell><x-ui::table.cell>C</x-ui::table.cell></x-ui::table.row></x-ui::data-table.content>')
        ->assertSeeMarkup('<div data-slot="data-table-content" data-sticky-header="data-sticky-header">')
        ->assertSeeMarkup('<th data-slot="table-head" data-sticky="start">A</th>')
        ->assertSeeMarkup('<td data-slot="table-cell" data-sticky="end">B</td>')
        ->assertSeeMarkup('<td data-slot="table-cell">C</td>');
});

it('orders the columns the user moves, keeps new ones after them, and resets them', function () {
    $columns = ['name' => 'Name', 'email' => 'Email', 'role' => 'Role'];

    $component = Livewire::test(Payments::class)
        ->call('moveColumn', 'role', 0, ['name', 'email', 'role'])
        ->assertSet('columnOrder', ['role', 'name', 'email'])
        ->call('moveColumn', 'name', 2, ['name', 'email', 'role'])
        ->assertSet('columnOrder', ['role', 'email', 'name'])
        ->call('toggleColumn', 'email');

    expect($component->instance()->orderedColumns($columns + ['plan' => 'Plan']))->toBe(['role' => 'Role', 'email' => 'Email', 'name' => 'Name', 'plan' => 'Plan'])
        ->and($component->instance()->visibleColumns($columns))->toBe(['role' => 'Role', 'name' => 'Name']);

    $component->call('resetColumns')
        ->assertSet('columnOrder', [])
        ->assertSet('hiddenColumns', []);
});

it('restores the default column layout on a fresh page visit', function () {
    Livewire::test(Payments::class)
        ->call('moveColumn', 'amount', 0, ['email', 'amount'])
        ->call('toggleColumn', 'email');

    Livewire::test(Payments::class)
        ->assertSet('columnOrder', [])
        ->assertSet('hiddenColumns', []);
});

it('renders reorderable view options with drag handles, in the user\'s order', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toContainMarkup('<div data-slot="data-table-column-list" wire:sort="moveColumn" wire:sort:group-id="email,amount">')
        ->toContainMarkup('<div data-slot="data-table-column-option" wire:sort:item="email" wire:key="data-table-column-email">')
        ->toContainMarkup('<span data-slot="data-table-column-handle" wire:sort:handle aria-hidden="true">')
        ->not->toContain('data-data-table-reset-columns');

    $html = Livewire::test(Payments::class)->call('moveColumn', 'amount', 0, 'email,amount')->html();

    expect(strpos($html, 'wire:sort:item="amount"'))->toBeLessThan(strpos($html, 'wire:sort:item="email"'))
        ->and($html)->toMatch('/wire:click="resetColumns" data-data-table-reset-columns="data-data-table-reset-columns">\s*Reset columns/');

    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    expect(trim($xpath->evaluate('string(//thead//th[2])')))->toBe('Amount')
        ->and(trim($xpath->evaluate('string(//tbody/tr[1]/td[2])')))->toBe('12')
        ->and(trim($xpath->evaluate('string(//tfoot//td[2])')))->toBe('35')
        ->and(trim($xpath->evaluate('string(//thead//th[last()])')))->toBe('Status');
});
