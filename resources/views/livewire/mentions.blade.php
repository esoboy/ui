<div data-mention-query="{{ json_encode($this->queryToken, JSON_THROW_ON_ERROR) }}"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby'])->class('ui-mentions-body') }}
    x-data="uiMentions(@js($mode))" x-on:keydown="navigate($event)">
    <x-ui::textarea :size="$size" :value="$this->current ?? ''" x-ref="input" x-on:input="input($event)" :disabled="$disabled"
        :readonly="$readonly"
        {{ $attributes->only(['id', 'name', 'form'])->merge($attributes->whereStartsWith('aria-')->all()) }} />
    @if ($this->queryToken !== null && ! $disabled && ! $readonly)
        <div class="ui-mentions-suggestions"
            wire:key="mentions-{{ $this->getId() }}-{{ md5($this->queryToken) }}"
            x-show="query === @js($this->queryToken) && !$wire.disabled && !$wire.readonly" x-cloak>
            @if ($this->suggestions === [])
                @include('ui::partials.empty-state', ['emptyMessage' => $this->queryToken === '' ? 'members' : 'member_matches'])
            @else
                <x-ui::scroll-area class="ui-list-scroll" tabindex="-1" role="listbox">
                    @foreach ($this->suggestions as $option)
                        <x-ui::button role="option" variant="ghost" class="ui-transfer-item" :disabled="$disabled || $readonly || $option['disabled']"
                            :wire:click="'choose('.\Illuminate\Support\Js::from($option['value']).', '.\Illuminate\Support\Js::from($this->queryToken).')'"
                            wire:key="mention-{{ $option['value'] }}">{{ $option['label'] }}</x-ui::button>
                    @endforeach
                </x-ui::scroll-area>
            @endif
        </div>
    @endif
</div>
