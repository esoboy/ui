<eso-transfer {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge(['data-slot' => 'transfer', 'data-disabled' => (bool) $disabled, ...($settings['validation'] ?? [])]) }}>
    @foreach ($lists as $list => $data)
    <div role="group" data-slot="transfer-list" data-list="{{ $list }}" aria-label="{{ $data['title'] }}">
        <div data-slot="transfer-header">
            <x-ui::checkbox data-transfer-all :aria-label="$data['title']" :disabled="$disabled" :checked="$data['all']" wire:click="toggleAll('{{ $list }}')" :x-effect="'$el.indeterminate = '.json_encode($data['mixed'])" />
            <span data-slot="transfer-title">{{ $data['title'] }}</span>
            <span data-slot="transfer-count">{{ $data['checked'].'/'.count($data['options']) }}</span>
        </div>
        @if ($searchable)
        <div data-slot="transfer-search">
            <x-ui::input-group>
                <x-ui::input-group.input type="search" data-transfer-search wire:model.live.debounce.150ms="query.{{ $list }}" :placeholder="__('ui::transfer.search')" :aria-label="__('ui::transfer.search')" autocomplete="off" />
                <x-ui::input-group.addon><x-ui::icon name="search" /></x-ui::input-group.addon>
            </x-ui::input-group>
        </div>
        @endif
        <div data-slot="transfer-items">
            @foreach ($data['options'] as $index => $option)
            <label data-slot="transfer-item" data-value="{{ $option['value'] }}" data-index="{{ $index }}" wire:key="transfer-{{ $list }}-{{ $option['value'] }}" @if (! $this->matches($list, $option)) hidden @endif>
                <x-ui::checkbox wire:model.live="checked" :value="$option['value']" :checked="in_array($option['value'], $checked, true)" :disabled="$disabled || ! empty($option['disabled'])" />
                <span>{{ isset($settings['optionView']) ? view($settings['optionView'], ['option' => $option, 'list' => $list]) : $option['label'] }}</span>
            </label>
            @endforeach
        </div>
        <div data-slot="transfer-empty" @if (! $data['empty']) hidden @endif>{{ __('ui::transfer.empty') }}</div>
    </div>
    @if ($list === 'source')
    <div data-slot="transfer-actions">
        @if ($slots->has('actions'))
            {{ $slots('actions') }}
        @elseif ($settings['actionsView'] ?? null)
            @include($settings['actionsView'], ['lists' => $lists, 'disabled' => $disabled])
        @else
        <x-ui::button variant="outline" size="icon-sm" data-transfer-move="target" :aria-label="__('ui::transfer.to_target')" wire:click="move('target')" :disabled="$disabled || ! $data['checked']"><x-ui::icon name="chevron-right" class="rtl:rotate-180" /></x-ui::button>
        <x-ui::button variant="outline" size="icon-sm" data-transfer-move="source" :aria-label="__('ui::transfer.to_source')" wire:click="move('source')" :disabled="$disabled || ! $lists['target']['checked']"><x-ui::icon name="chevron-left" class="rtl:rotate-180" /></x-ui::button>
        @endif
    </div>
    @endif
    @endforeach
    @if ($slots->has('default')){{ $slots }}@endif
    @if ($attributes->has('name'))
        @foreach ($value as $item)
        <input type="hidden" name="{{ $attributes->get('name') }}[]" value="{{ $item }}">
        @endforeach
    @endif
</eso-transfer>
