@php
    $units = $seconds ? ['hour', 'minute', 'second'] : ['hour', 'minute'];
    $parts = $time === null ? [] : explode(':', $time);
    $panelId = $this->getId().'-time';
@endphp
<div class="ui-time-panel" wire:key="time-panel-{{ count($units) }}{{ $awaitingDate ? '-awaiting-date' : '' }}"
    x-data="uiTimePanel"
    x-on:keydown.stop x-effect="syncUnit(opened)">
    <div class="ui-time-segments" role="group" aria-label="{{ __('ui::ui.select_time') }}"
        x-on:keydown.arrow-down.prevent="openUnit($event.target.dataset.value)">
        @foreach ($units as $index => $unit)
            @unless ($loop->first)<span class="ui-time-colon" aria-hidden="true">:</span>@endunless
            <button class="ui-time-segment" type="button" data-value="{{ $unit }}" id="{{ $panelId }}-{{ $unit }}"
                aria-controls="{{ $panelId }}-{{ $unit }}-options" @disabled($awaitingDate)
                x-ref="{{ $unit }}Trigger" x-bind:aria-expanded="active === '{{ $unit }}'"
                x-on:click="toggleUnit($el.dataset.value, $event.detail === 0)">
                <span class="ui-time-digits">{{ $parts[$index] ?? '––' }}</span>
                <span>{{ __('ui::ui.'.$unit) }}</span>
            </button>
        @endforeach
    </div>
    @foreach ($units as $index => $unit)
        <div class="ui-time-unit" role="region" id="{{ $panelId }}-{{ $unit }}-options"
            aria-labelledby="{{ $panelId }}-{{ $unit }}" x-ref="{{ $unit }}" tabindex="-1" x-cloak x-show="active === '{{ $unit }}'">
            <x-ui::scroll-area class="ui-list-scroll ui-time-list" tabindex="-1">
                <div role="listbox" aria-label="{{ __('ui::ui.'.$unit) }}" class="ui-time-picker-grid" data-time-grid="{{ $unit }}"
                    x-data="uiOptions('button')" x-on:keydown="navigateGrid($event, 5)"
                    x-on:focusin="tabStop($event.target)"
                    x-on:click="finishUnit($event.target, $el.dataset.timeGrid)">
                    @foreach (range(0, $unit === 'hour' ? 23 : 59) as $number)
                        @php($digits = sprintf('%02d', $number))
                        <button type="button" role="option" aria-selected="{{ ($parts[$index] ?? null) === $digits ? 'true' : 'false' }}"
                            tabindex="{{ ($parts[$index] ?? '00') === $digits ? 0 : -1 }}"
                            @disabled($disabled || $readonly || $awaitingDate)
                            wire:click="chooseTime('{{ $unit }}', {{ $number }})">{{ $digits }}</button>
                    @endforeach
                </div>
            </x-ui::scroll-area>
        </div>
    @endforeach
    @if ($awaitingDate)
        <p class="ui-time-hint">{{ __('ui::ui.choose_date_first') }}</p>
    @endif
</div>
