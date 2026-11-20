@php($monthYearFormat = __('ui::ui.month_year_format', [], \Esoboy\Ui\Support\Locale::normalize($locale ?? app()->getLocale())))
<div {{ ($calendarAttributes ?? new \Illuminate\View\ComponentAttributeBag)->class(['group/calendar'])->merge(['data-esoboy-ui' => '', 'data-slot' => 'calendar']) }} x-data="uiOptions('[data-calendar-day]')" x-on:keydown="navigateGrid($event, 7, true)">
    <div data-esoboy-ui data-slot="calendar-header">
        <x-ui::button variant="ghost" size="icon-sm" wire:click="moveMonth(-1)" :disabled="$disabled || ($this->navigationStart !== null && $this->period->toDateString() <= $this->navigationStart)"
            :aria-label="__('ui::ui.previous_month')"><x-ui::icon name="chevron-left" /></x-ui::button>
        @if ($captionLayout === 'dropdown')
            <div data-esoboy-ui data-slot="calendar-dropdowns">
                <span>
                    <select aria-label="{{ __('ui::ui.select_month') }}" wire:change="showMonthOfYear($event.target.value)" @disabled($disabled)>
                        @foreach ($this->months as $option)
                            <option value="{{ $option['value'] }}" @selected($this->period->month === $option['value']) @disabled($option['disabled'])>{{ $monthLabelFormat === 'short' ? $this->period->setMonth($option['value'])->isoFormat('MMM') : $option['label'] }}</option>
                        @endforeach
                    </select>
                    <x-ui::icon name="chevron-down" />
                </span>
                <span>
                    <select aria-label="{{ __('ui::ui.select_year') }}" wire:change="showYear($event.target.value)" @disabled($disabled)>
                        @foreach ($this->years as $option)
                            <option value="{{ $option['value'] }}" @selected($this->period->year === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <x-ui::icon name="chevron-down" />
                </span>
            </div>
        @elseif ($numberOfMonths === 1)
            <p aria-live="polite">{{ $this->period->isoFormat($monthYearFormat) }}</p>
        @endif
        <x-ui::button variant="ghost" size="icon-sm" wire:click="moveMonth(1)" :disabled="$disabled || $this->month === $this->lastPageMonth"
            :aria-label="__('ui::ui.next_month')"><x-ui::icon name="chevron-right" /></x-ui::button>
    </div>
    <div data-esoboy-ui data-slot="calendar-months">
        @for ($monthOffset = 0; $monthOffset < $numberOfMonths; $monthOffset++)
            @php($visiblePeriod = $this->period->addMonths($monthOffset))
            <div data-esoboy-ui data-slot="calendar-month">
                @if ($numberOfMonths > 1)<p data-esoboy-ui data-slot="calendar-month-caption">{{ $visiblePeriod->isoFormat($monthYearFormat) }}</p>@endif
                <table role="grid" aria-label="{{ $visiblePeriod->isoFormat($monthYearFormat) }}" data-esoboy-ui data-slot="calendar-grid">
                <thead><tr>
                    @if ($showWeekNumber)<th scope="col" data-esoboy-ui data-slot="calendar-week-number-header" aria-label="{{ __('ui::ui.week_number') }}"></th>@endif
                    @for ($weekday = 0; $weekday < 7; $weekday++)
                        <th scope="col">{{ $visiblePeriod->startOfWeek($weekStartsOn)->addDays($weekday)->isoFormat('dd') }}</th>
                    @endfor
                </tr></thead>
                <tbody>
                    @foreach (array_chunk($this->monthDays($monthOffset), 7) as $week)
                        <tr>
                            @if ($showWeekNumber)<th scope="row" data-esoboy-ui data-slot="calendar-week-number">{{ str_pad((string) \Carbon\CarbonImmutable::parse($week[0]['date'])->week(null, $weekStartsOn, 1), 2, '0', STR_PAD_LEFT) }}</th>@endif
                            @foreach ($week as $day)
                                <td class="{{ $dayClasses[$day['date']] ?? '' }}" data-hidden="{{ $day['hidden'] ? 'true' : 'false' }}" data-disabled="{{ $day['disabled'] ? 'true' : 'false' }}" aria-selected="{{ $day['selected'] || $day['inRange'] ? 'true' : 'false' }}" data-today="{{ $day['today'] ? 'true' : 'false' }}" data-range="{{ $day['inRange'] ? 'middle' : ($range && $day['selected'] ? ($day['date'] === $this->selected[0] ? 'start' : 'end') : '') }}">
                                    <x-ui::button variant="ghost" size="icon" data-calendar-day :data-selected-single="$day['selected'] ? 'true' : 'false'" :data-range-middle="$day['inRange'] ? 'true' : 'false'" :data-outside="$day['current'] ? 'false' : 'true'" :aria-label="$day['date']" :aria-current="$day['today'] ? 'date' : null"
                                        :disabled="$disabled || $readonly || $day['disabled']" :wire:click="'choose('.\Illuminate\Support\Js::from($day['date']).')'"
                                        wire:key="day-{{ $monthOffset }}-{{ $day['date'] }}">{{ $day['label'] }}@if($day['current'] && isset($dayLabels[$day['date']]))<span>{{ $dayLabels[$day['date']] }}</span>@endif</x-ui::button>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endfor
    </div>
</div>
