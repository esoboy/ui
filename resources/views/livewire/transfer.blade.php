<div data-slot="transfer" {{ $attributes->whereDoesntStartWith('wire:model')->except('name') }}>
    <x-ui::input :id="$this->getId() . '-query'" wire:model.live.debounce.200ms="query" type="search" :placeholder="__('ui::ui.search')" :aria-label="__('ui::ui.search')"
        :disabled="$disabled" />
    <div class="ui-transfer-columns">
        @foreach ($this->panels as $side => $panel)
            @php($selectedSide = $side === 'selected')
            <x-ui::card>
                <x-ui::card.header><x-ui::card.title>{{ __('ui::ui.'.$side) }}</x-ui::card.title></x-ui::card.header>
                <x-ui::card.content>
                    <x-ui::scroll-area class="ui-list-scroll ui-transfer-list" tabindex="-1">
                        @forelse ($panel['items'] as $option)
                            <x-ui::button variant="ghost" class="ui-transfer-item"
                                :wire:click="'choose('.\Illuminate\Support\Js::from($option['value']).')'"
                                :disabled="$disabled || $readonly || $option['disabled']"
                                wire:key="transfer-{{ $side }}-{{ $option['value'] }}"><span>{{ $option['label'] }}</span><x-ui::icon
                                    :name="$selectedSide ? 'arrow-left' : 'arrow-right'" /></x-ui::button>
                        @empty
                            @include('ui::partials.empty-state', ['emptyMessage' => $panel['emptyMessage']])
                        @endforelse
                    </x-ui::scroll-area>
                </x-ui::card.content>
                <x-ui::card.footer><x-ui::button variant="outline"
                        wire:click="moveAll({{ $selectedSide ? 'false' : 'true' }})"
                        :disabled="$disabled || $readonly">{{ __('ui::ui.' . ($selectedSide ? 'remove' : 'add')) }}</x-ui::button></x-ui::card.footer>
            </x-ui::card>
        @endforeach
    </div>
    @include('ui::livewire.partials.selection-input')
</div>
