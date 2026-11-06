@if ($settings['searchable'] ?? false)
    <div data-slot="tree-search">
        <x-ui::input-group>
            <x-ui::input-group.input data-tree-search :placeholder="$settings['placeholder'] ?? __('ui::tree.search')" :aria-label="__('ui::tree.search')" autocomplete="off" spellcheck="false" class="px-2 text-sm" wire:model.live.debounce.150ms="query" :disabled="$settings['disabled'] ?? false" />
            <x-ui::input-group.addon><x-ui::icon name="search" /></x-ui::input-group.addon>
        </x-ui::input-group>
    </div>
@endif
<ul role="tree" data-slot="tree-list" {{ $attributes->only(['aria-label', 'aria-labelledby']) }} @if ($settings['checkable'] ?? false) aria-multiselectable="true" @endif>
    @include('ui::livewire.tree-nodes', ['nodes' => $nodes, 'level' => 1, 'ancestorDisabled' => $settings['disabled'] ?? false])
</ul>
@if ($settings['searchable'] ?? false)
    <div data-slot="tree-empty" @if (array_filter($nodes, fn ($item) => $this->matches($item))) hidden @endif>{{ __('ui::tree.no_results') }}</div>
@endif
@if ($slots->has('default')){{ $slots }}@endif
@if ($attributes->has('name'))
    @foreach (($settings['checkable'] ?? false) ? (array) $value : [$value] as $selected)
        <input type="hidden" name="{{ $attributes->get('name').(($settings['checkable'] ?? false) ? '[]' : '') }}" value="{{ $selected }}">
    @endforeach
@endif
