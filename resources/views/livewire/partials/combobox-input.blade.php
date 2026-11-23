    <x-ui::input-group data-slot="combobox-control" :data-ui-anchor-box="$searchInPopup ? null : true" :class="$multiple ? 'ui-combobox-multiple' : null">
        @if ($multiple)
            @foreach ($this->selectedChoices as $option)
                    <span data-esoboy-ui data-slot="combobox-chip" tabindex="-1" data-value="{{ json_encode($option['value']) }}" wire:key="chip-{{ $option['value'] }}">{{ $option['label'] }}
                        @if($showRemove)<x-ui::button variant="ghost" size="icon-xs" data-slot="combobox-chip-remove" x-on:mousedown.prevent x-on:click="$root.querySelector('input[role=combobox]')?.focus({preventScroll: true})" :wire:click="'choose('.\Illuminate\Support\Js::from($option['value']).')'" :disabled="$disabled || $readonly || $option['disabled']" :aria-label="__('ui::ui.remove_item', ['label' => $option['label']])"><x-ui::icon name="x" /></x-ui::button>@endif
                    </span>
            @endforeach
        @endif
        <span data-ui-trigger>
            <x-ui::input-group.input type="text" role="combobox" aria-autocomplete="list" autocomplete="off"
                id="{{ $attributes->get('id') ? $attributes->get('id').($searchInPopup ? '-search' : '') : null }}" {{ $attributes->whereStartsWith('aria-')->merge(['aria-label' => __('ui::ui.search')]) }}
                wire:model.live.debounce.200ms="query" value="{{ $query }}" aria-controls="{{ $this->getId() }}-list" x-bind:aria-expanded="opened"
                placeholder="{{ $searchInPopup ? $searchPlaceholder : ($multiple || $placeholder !== '' ? $placeholder : __('ui::ui.select')) }}"
                data-selected="{{ $this->label !== '' ? 'true' : 'false' }}" :disabled="$disabled" :readonly="$readonly"
                x-on:keydown.escape="if (!opened && !panel.matches(':popover-open')) { $event.stopPropagation(); {{ ($paginated ?? false) ? 'selectIcon(null)' : '$wire.clearSelection()' }} }"
                x-on:pointerdown="if ($event.button === 0 && $event.pointerType === 'mouse') open(false)" x-on:click="open(false)" x-on:input="open(false); highlightOption(null)" x-on:keydown.arrow-down.stop="navigate($event)" x-on:keydown.arrow-up.stop="navigate($event)" />
        </span>
        @if(!$multiple)
        <x-ui::input-group.addon align="inline-end">
            @if ($clearable && $this->selected !== [])
                <x-ui::input-group.button size="icon-xs" data-slot="combobox-clear" x-on:mousedown.prevent x-on:click="$root.querySelector('input[role=combobox]')?.focus({preventScroll: true}); {{ ($paginated ?? false) ? 'selectIcon(null)' : '' }}" :wire:click="($paginated ?? false) ? null : 'clearSelection'" aria-label="{{ __('ui::ui.clear') }}" :disabled="$disabled || $readonly"><x-ui::icon name="x" /></x-ui::input-group.button>
            @elseif($showTrigger)
                <x-ui::input-group.button size="icon-xs" data-slot="combobox-trigger" x-on:click="toggle()" x-bind:aria-expanded="opened" aria-controls="{{ $this->getId() }}-list" aria-label="{{ __('ui::ui.select') }}" :disabled="$disabled"><x-ui::icon name="chevron-down" class="ui-select-chevron" /></x-ui::input-group.button>
            @endif
        </x-ui::input-group.addon>
        @endif
        @if($prefixIcon)<x-ui::input-group.addon><x-ui::icon :name="$prefixIcon" /></x-ui::input-group.addon>@endif
    </x-ui::input-group>
