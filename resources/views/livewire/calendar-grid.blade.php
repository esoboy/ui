<div data-slot="calendar-months">
    <nav data-slot="calendar-nav">
        <x-ui::button variant="ghost" size="icon" data-calendar-previous wire:click="previous" :aria-disabled="! $this->canShow(\Carbon\CarbonImmutable::parse($month.'-01')->subMonth()->format('Y-m')) ? 'true' : null" :aria-label="__('ui::calendar.previous')"><x-ui::icon name="chevron-left" /></x-ui::button>
        <x-ui::button variant="ghost" size="icon" data-calendar-next wire:click="next" :aria-disabled="! $this->canShow(\Carbon\CarbonImmutable::parse($month.'-01')->addMonths(count($months))->format('Y-m')) ? 'true' : null" :aria-label="__('ui::calendar.next')"><x-ui::icon name="chevron-right" /></x-ui::button>
    </nav>
    @foreach ($months as $display)
        <div data-slot="calendar-month" wire:key="month-{{ $display['key'] }}">
            <div data-slot="calendar-caption">
                @if (($settings['captionLayout'] ?? 'label') === 'dropdown')
                    <div data-slot="calendar-dropdowns">
                        <div data-slot="calendar-dropdown">
                            <x-ui::native-select size="sm" data-calendar-month :aria-label="__('ui::calendar.month')" wire:change="goTo('{{ $display['year'] }}-' + String(Number($event.target.value) + 1).padStart(2, '0'))">
                                @foreach (range(1, 12) as $number)
                                    @php $optionMonth = sprintf('%04d-%02d', $display['year'], $number); @endphp
                                    <option value="{{ $number - 1 }}" @selected($number === $display['month']) @disabled(! $this->canShow($optionMonth))>{{ $this->format($optionMonth.'-01', 'MMM') }}</option>
                                @endforeach
                            </x-ui::native-select>
                        </div>
                        <div data-slot="calendar-dropdown">
                            @php
                                $startYear = (int) substr($settings['startMonth'] ?? $settings['min'] ?? (string) ($display['year'] - 100), 0, 4);
                                $endYear = (int) substr($settings['endMonth'] ?? $settings['max'] ?? (string) ($display['year'] + 10), 0, 4);
                            @endphp
                            <x-ui::native-select size="sm" data-calendar-year :aria-label="__('ui::calendar.year')" wire:change="goTo($event.target.value + '-{{ sprintf('%02d', $display['month']) }}')">
                                @foreach (range(min($startYear, $display['year']), max($endYear, $display['year'])) as $year)
                                    <option value="{{ $year }}" @selected($year === $display['year'])>{{ $this->format($year.'-01-01', 'y') }}</option>
                                @endforeach
                            </x-ui::native-select>
                        </div>
                    </div>
                @else
                    <span data-slot="calendar-caption-label" aria-live="polite">{{ $display['caption'] }}</span>
                @endif
            </div>
            <table data-slot="calendar-grid" role="grid">
                <thead><tr data-slot="calendar-weekdays">
                    @foreach ($display['weekdays'] as $weekday)
                        <th data-slot="calendar-weekday" scope="col" aria-label="{{ $weekday['label'] }}">{{ $weekday['text'] }}</th>
                    @endforeach
                </tr></thead>
                <tbody>
                    @foreach ($display['weeks'] as $week)
                        <tr data-slot="calendar-week">
                            @foreach ($week as $day)
                                @if ($day['hidden'])
                                    <td data-slot="calendar-day" data-hidden="true"></td>
                                @else
                                    <td data-slot="calendar-day" role="gridcell" {{ new \Illuminate\View\ComponentAttributeBag($day['flags']) }}>
                                        <x-ui::button variant="ghost" size="icon" :data-day="$day['key']" :aria-label="$day['label']" :aria-pressed="$day['selected'] ? 'true' : null" :aria-disabled="$day['disabled'] ? 'true' : null" :tabindex="$day['focus'] ? 0 : -1" wire:click="select('{{ $day['key'] }}')" wire:key="day-{{ $display['key'] }}-{{ $day['key'] }}" :attributes="new \Illuminate\View\ComponentAttributeBag($day['flags'])">{{ isset($settings['dayView']) ? view($settings['dayView'], ['day' => $day]) : $day['number'] }}</x-ui::button>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
