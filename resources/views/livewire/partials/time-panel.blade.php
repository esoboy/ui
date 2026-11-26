@php
    $units = $seconds ? ['hour', 'minute', 'second'] : ['hour', 'minute'];
    $parts = $time === null ? [] : explode(':', $time);
    $panelId = $this->getId().'-time';
@endphp
<div class="ui-time-panel" data-size="{{ $size }}" wire:key="time-panel-{{ count($units) }}{{ $awaitingDate ? '-awaiting-date' : '' }}"
    x-data="uiTimePanel"
    x-on:keydown.stop x-effect="syncUnit(opened)">
    <div class="ui-time-segments" role="group" aria-label="{{ __('ui::ui.select_time') }}"
        wire:keydown.arrow-down.prevent="$set('activeUnit', $event.target.dataset.value, false)" x-on:keydown.arrow-down="revealTime(true, $event.target.dataset.value)">
        @foreach ($units as $index => $unit)
            @unless ($loop->first)<span class="ui-time-colon" aria-hidden="true">:</span>@endunless
            <button class="ui-time-segment" type="button" data-value="{{ $unit }}" id="{{ $panelId }}-{{ $unit }}"
                aria-controls="{{ $panelId }}-{{ $unit }}-options" @disabled($awaitingDate)
                x-ref="{{ $unit }}Trigger" wire:bind:aria-expanded="activeUnit === '{{ $unit }}'"
                wire:click="$set('activeUnit', activeUnit === '{{ $unit }}' ? null : '{{ $unit }}', false)"
                x-on:click="if ($event.detail === 0) revealTime(true, $el.dataset.value)">
                <span class="ui-time-digits">{{ $parts[$index] ?? '––' }}</span>
                <span>{{ __('ui::ui.'.$unit) }}</span>
            </button>
        @endforeach
    </div>
    @foreach ($units as $index => $unit)
        <div class="ui-time-unit" role="region" id="{{ $panelId }}-{{ $unit }}-options"
            aria-labelledby="{{ $panelId }}-{{ $unit }}" x-ref="{{ $unit }}" tabindex="-1" wire:cloak wire:show="activeUnit === '{{ $unit }}'">
            <x-ui::scroll-area class="ui-list-scroll ui-time-list" tabindex="-1">
                <div role="listbox" aria-label="{{ __('ui::ui.'.$unit) }}" class="ui-time-picker-grid" data-time-grid="{{ $unit }}"
                    x-data="uiOptions('button')" x-on:keydown="navigateGrid($event, 5)"
                    x-on:focusin="tabStop($event.target)"
                    x-on:click="if ($event.target.matches('button:enabled')) $nextTick(() => $refs.{{ $unit }}Trigger.focus())">
                    @foreach (range(0, $unit === 'hour' ? 23 : 59) as $number)
                        @php($digits = sprintf('%02d', $number))
                        <button type="button" role="option" aria-selected="{{ ($parts[$index] ?? null) === $digits ? 'true' : 'false' }}"
                            tabindex="{{ ($parts[$index] ?? '00') === $digits ? 0 : -1 }}"
                            @disabled($disabled || $readonly || $awaitingDate)
                            @if ($mode === 'controlled') wire:click="chooseTime('{{ $unit }}', {{ $number }})" @else {{ $this->fieldEdit($this->timeSelection($unit, $number)) }} data-part="{{ (($withTime ?? false) ? 11 : 0) + $index * 3 }}" data-width="2" wire:click="$set('activeUnit', null, false)" @endif>{{ $digits }}</button>
                    @endforeach
                </div>
            </x-ui::scroll-area>
        </div>
    @endforeach
    @if ($awaitingDate)
        <p class="ui-time-hint">{{ __('ui::ui.choose_date_first') }}</p>
    @endif
</div>
