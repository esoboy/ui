<?php

/*
 * Data Table: the WithDataTable state and the parts that read it.
 */

use Esoboy\Ui\Tests\Fixtures\Payments;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

it('sorts a column ascending, then descending, back on the first page', function () {
    Livewire::test(Payments::class)
        ->call('setPage', 3)
        ->call('sort', 'email')
        ->assertSet('sortBy', 'email')
        ->assertSet('sortDirection', 'asc')
        ->assertSet('paginators.page', 1)
        ->call('sort', 'email')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'email', 'asc')
        ->assertSet('sortDirection', 'asc');
});

it('applies only allowed sorts to the query', function () {
    $component = Livewire::test(Payments::class);

    expect($component->instance()->sortedSql())->not->toContain('order by');

    $component->call('sort', 'email', 'desc');
    expect($component->instance()->sortedSql())->toContain('order by "email" desc');

    $component->call('sort', 'password');
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

it('renders sortable headers with their sort menu', function () {
    $html = Livewire::test(Payments::class)->call('sort', 'email', 'desc')->html();

    expect($html)
        ->toContain('<div data-slot="data-table-column-header">')
        ->toContain('wire:click="sort(\'email\', \'asc\')"')
        ->toContain('wire:click="sort(\'email\', \'desc\')"')
        ->toContain('wire:click="toggleColumn(\'email\')"')
        ->toContain('<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>')
        ->toContain('<div >Status</div>');
});

it('renders the selection boxes bound to the selected rows', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toContain('<eso-data-table data-slot="data-table">')
        ->toMatch('/data-data-table-select-all="data-data-table-select-all" aria-label="Select all"/')
        ->toMatch('/wire:model.live="selected" value="1" data-data-table-select-row="data-data-table-select-row" aria-label="Select row"/')
        ->toContain('<td data-slot="table-cell" colspan="3" data-data-table-empty="data-data-table-empty">')
        ->toContain('No results.');
});

it('renders the pagination with count, page size and page buttons', function () {
    $html = Livewire::test(Payments::class)->set('selected', [1, 2])->call('setPage', 2)->html();

    expect($html)
        ->toContain('<div data-slot="data-table-selection">2 of 42 row(s) selected.</div>')
        ->toContain('<p>Rows per page</p>')
        ->toContain('wire:model.live="perPage"')
        ->toContain('<div data-slot="data-table-page">Page 2 of 5</div>')
        ->toContain('wire:click="setPage(1, \'page\')"')
        ->toContain('wire:click="previousPage(\'page\')"')
        ->toContain('wire:click="setPage(5, \'page\')"')
        ->toContain('<span class="sr-only">Go to first page</span>');
});

it('resets to the first page when the page size changes', function () {
    Livewire::test(Payments::class)
        ->call('setPage', 4)
        ->set('perPage', 20)
        ->assertSet('paginators.page', 1);
});

it('takes only the page sizes the pager offers, from the URL or the browser', function () {
    expect(Livewire::test(Payments::class)->html())
        ->toContain('data-value="25"')
        ->toContain('data-value="50"');

    Livewire::withQueryParams(['perPage' => 1000000])
        ->test(Payments::class)
        ->assertSet('perPage', 10)
        ->set('perPage', 30)
        ->assertSet('perPage', 30)
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

it('renders the faceted filter, the reset button and a header filter from the filters', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toMatch('/data-variant="outline" data-size="sm"[^>]*data-data-table-faceted-filter="data-data-table-faceted-filter"/')
        ->toMatch("/data-value=\"paid\"[^>]*wire:click=\"toggleFilter\\('status', 'paid'\\)\"/")
        ->toContain('<span data-slot="data-table-filter-count">3</span>')
        ->not->toContain('data-chosen')
        ->not->toContain('data-data-table-reset')
        ->toContain("<input type=\"checkbox\" role=\"menuitemcheckbox\" wire:click=\"toggleFilter('method', 'card')\">");

    $html = Livewire::test(Payments::class)->call('toggleFilter', 'status', 'paid')->html();

    expect($html)
        ->toMatch('/data-value="paid"[^>]*data-chosen="data-chosen"/')
        ->toContain('<span data-data-table-filter-values>')
        ->toContain('Paid</span>')
        ->toMatch("/wire:click=\"clearFilters\\('status'\\)\">\\s*Clear filters/")
        ->toMatch('/wire:click="resetFilters" data-data-table-reset="data-data-table-reset">\s*Reset/');
});

it('renders expandable and tree rows, starting children hidden', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::data-table.row id="eng" :level="1" expanded><x-ui::table.cell><x-ui::data-table.row-toggle /></x-ui::table.cell></x-ui::data-table.row>
        <x-ui::data-table.row id="eng/api" parent="eng" :level="2"><x-ui::table.cell>API</x-ui::table.cell></x-ui::data-table.row>
        <x-ui::data-table.row-detail for="eng" :colspan="4" class="p-4">Details</x-ui::data-table.row-detail>
        BLADE);

    expect($html)
        ->toContain('<tr data-slot="table-row" style="--level: 1;" wire:key="data-table-row-eng" data-row-id="eng" data-default-expanded="data-default-expanded">')
        ->toMatch('/data-data-table-row-toggle="data-data-table-row-toggle" aria-expanded="false" aria-label="Expand row"/')
        ->toContain('<tr data-slot="table-row" style="--level: 2;" wire:key="data-table-row-eng/api" data-row-id="eng/api" data-parent="eng" hidden="hidden">')
        ->toContain('<tr data-slot="data-table-row-detail" data-detail-for="eng" wire:key="data-table-row-detail-eng" hidden>')
        ->toContain('<td colspan="4" data-slot="table-cell" class="p-4">Details</td>');
});

it('fixes columns and the header', function () {
    $this->blade('<x-ui::data-table.content sticky-header><x-ui::table.row><x-ui::table.head sticky="start">A</x-ui::table.head><x-ui::table.cell sticky="end">B</x-ui::table.cell><x-ui::table.cell>C</x-ui::table.cell></x-ui::table.row></x-ui::data-table.content>')
        ->assertSee('<div data-slot="data-table-content" data-sticky-header="data-sticky-header">', false)
        ->assertSee('<th data-slot="table-head" data-sticky="start">A</th>', false)
        ->assertSee('<td data-slot="table-cell" data-sticky="end">B</td>', false)
        ->assertSee('<td data-slot="table-cell">C</td>', false);
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

it('keeps the order and visibility of the columns in the session', function () {
    Livewire::test(Payments::class)
        ->call('moveColumn', 'amount', 0, ['email', 'amount'])
        ->call('toggleColumn', 'email');

    Livewire::test(Payments::class)
        ->assertSet('columnOrder', ['amount', 'email'])
        ->assertSet('hiddenColumns', ['email']);
});

it('renders reorderable view options with drag handles, in the user\'s order', function () {
    $html = Livewire::test(Payments::class)->html();

    expect($html)
        ->toContain('<div data-slot="data-table-column-list" wire:sort="moveColumn" wire:sort:group-id="email,amount">')
        ->toContain('<div data-slot="data-table-column-option" wire:sort:item="email" wire:key="data-table-column-email">')
        ->toContain('<span data-slot="data-table-column-handle" wire:sort:handle aria-hidden="true">')
        ->not->toContain('data-data-table-reset-columns');

    $html = Livewire::test(Payments::class)->call('moveColumn', 'amount', 0, 'email,amount')->html();

    expect(strpos($html, 'wire:sort:item="amount"'))->toBeLessThan(strpos($html, 'wire:sort:item="email"'))
        ->and($html)->toMatch('/wire:click="resetColumns" data-data-table-reset-columns="data-data-table-reset-columns">\s*Reset columns/');
});
