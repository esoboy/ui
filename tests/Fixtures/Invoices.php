<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabs bound to a property and a paginated list.
 */
class Invoices extends Component
{
    use WithPagination;

    public string $tab = 'account';

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui::tabs wire:model="tab">
                    <x-ui::tabs.list>
                        <x-ui::tabs.trigger value="invoices">Invoices</x-ui::tabs.trigger>
                        <x-ui::tabs.trigger value="account">Account</x-ui::tabs.trigger>
                    </x-ui::tabs.list>
                </x-ui::tabs>
                <x-ui::pagination :paginator="$this->invoices()" />
            </div>
            BLADE;
    }

    /**
     * @return LengthAwarePaginator<int, int>
     */
    public function invoices(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(range(1, 5), 50, 5, $this->getPage());
    }
}
