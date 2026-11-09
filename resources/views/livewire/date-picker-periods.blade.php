@php
    $year = $periods['year'];
    $caption = $this->format(sprintf('%04d-01-01', $year), 'y');
@endphp
<div data-slot="calendar" data-granularity="{{ $settings['granularity'] }}">
    <div data-slot="calendar-months">
        <nav data-slot="calendar-nav" aria-label="{{ __('ui::calendar.year') }}">
            <x-ui::button variant="ghost" size="icon" data-date-picker-previous-year :disabled="! $periods['previous']" wire:click="goToYear({{ $year - 1 }})" :aria-label="__('ui::date-picker.previous-year')"><x-ui::icon name="chevron-left" /></x-ui::button>
            <x-ui::button variant="ghost" size="icon" data-date-picker-next-year :disabled="! $periods['next']" wire:click="goToYear({{ $year + 1 }})" :aria-label="__('ui::date-picker.next-year')"><x-ui::icon name="chevron-right" /></x-ui::button>
        </nav>
        <div data-slot="calendar-caption">
            @if (($settings['captionLayout'] ?? 'label') === 'dropdown')
                <div data-slot="calendar-dropdown">
                    <x-ui::native-select size="sm" data-calendar-year :aria-label="__('ui::calendar.year')" wire:change="goToYear($event.target.value)">
                        @foreach (range(min($periods['startYear'], $year), max($periods['endYear'], $year)) as $optionYear)
                            <option value="{{ $optionYear }}" @selected($optionYear === $year)>{{ $this->format(sprintf('%04d-01-01', $optionYear), 'y') }}</option>
                        @endforeach
                    </x-ui::native-select>
                </div>
            @else
                <span data-slot="calendar-caption-label" aria-live="polite">{{ $caption }}</span>
            @endif
        </div>
    </div>
    <div data-slot="date-picker-periods" role="group" aria-label="{{ $caption }}">
        @foreach ($periods['options'] as $option)
            <x-ui::button variant="ghost" size="sm" :data-date-picker-period="$option['key']" :aria-label="$option['label']" :aria-pressed="$option['pressed'] ? 'true' : 'false'" :data-range-middle="$option['middle']" :disabled="$option['disabled']" wire:key="period-{{ $option['key'] }}" wire:click="select('{{ $option['key'] }}')">{{ $option['text'] }}</x-ui::button>
        @endforeach
    </div>
</div>
