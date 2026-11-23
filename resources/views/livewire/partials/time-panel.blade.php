@php
    $units = $seconds ? ['hour', 'minute', 'second'] : ['hour', 'minute'];
    $parts = $time === null ? [] : explode(':', $time);
    $panelId = $this->getId().'-time';
@endphp
<div class="ui-time-panel" wire:key="time-panel-{{ count($units) }}{{ $awaitingDate ? '-awaiting-date' : '' }}"
    x-data="{
        active: null,
        tabStop(option) {
            const previous = option.parentElement.querySelector('[tabindex=&quot;0&quot;]')
            if (previous) previous.tabIndex = -1
            option.tabIndex = 0
        },
        revealTime(focus = false) {
            const unit = this.active
            this.$nextTick(() => {
                const panel = this.$refs[unit]
                if (!panel?.getClientRects().length) return
                const option = panel.querySelector('[aria-selected=true]') ?? panel.querySelector('button')
                const viewport = option.closest('.ui-scroll-area-viewport')
                viewport.scrollTop += option.getBoundingClientRect().top - viewport.getBoundingClientRect().top
                    - (viewport.clientHeight - option.offsetHeight) / 2
                this.tabStop(option)
                option.scrollIntoView({ block: 'nearest', inline: 'nearest' })
                if (focus) option.focus({ preventScroll: true })
            })
        },
    }"
    x-on:keydown.stop x-effect="if (!opened) active = null; else if (active) revealTime()">
    <div class="ui-time-segments" role="group" aria-label="{{ __('ui::ui.select_time') }}"
        x-on:keydown.arrow-down.prevent="active = $event.target.dataset.value; revealTime(true)">
        @foreach ($units as $index => $unit)
            @unless ($loop->first)<span class="ui-time-colon" aria-hidden="true">:</span>@endunless
            <button class="ui-time-segment" type="button" data-value="{{ $unit }}" id="{{ $panelId }}-{{ $unit }}"
                aria-controls="{{ $panelId }}-{{ $unit }}-options" @disabled($awaitingDate)
                x-ref="{{ $unit }}Trigger" x-bind:aria-expanded="active === '{{ $unit }}'"
                x-on:click="active = active === '{{ $unit }}' ? null : '{{ $unit }}'; if (active && $event.detail === 0) revealTime(true)">
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
                    x-on:click="if ($event.target.matches('button:enabled')) { active = null; $nextTick(() => $refs.{{ $unit }}Trigger.focus()) }">
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
