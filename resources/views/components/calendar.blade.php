@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'mode' => 'single',
    'defaultValue' => null,
    'defaultMonth' => null,
    'captionLayout' => 'label',
    'numberOfMonths' => 1,
    'showOutsideDays' => true,
    'weekStartsOn' => null,
    'min' => null,
    'max' => null,
    'disabledDates' => [],
    'startMonth' => null,
    'endMonth' => null,
])

@php
$value = $defaultValue ?? Model::value($attributes);
@endphp

{{-- <eso-calendar> renders the month grid in the app's locale and exposes the selection as .value (Y-m-d). --}}
<eso-calendar {{ $attributes->merge([
    'data-slot' => 'calendar',
    'data-mode' => $mode,
    'data-value' => $value === null ? false : json_encode($value),
    'data-default-month' => $defaultMonth,
    'data-caption-layout' => $captionLayout,
    'data-number-of-months' => $numberOfMonths,
    'data-show-outside-days' => $showOutsideDays ? 'true' : 'false',
    'data-week-starts-on' => $weekStartsOn,
    'data-min' => $min,
    'data-max' => $max,
    'data-disabled-dates' => $disabledDates ? json_encode(array_values((array) $disabledDates)) : false,
    'data-start-month' => $startMonth,
    'data-end-month' => $endMonth,
    'data-locale' => str_replace('_', '-', app()->getLocale()),
]) }} wire:ignore>
    {{-- The parts the script builds the months from, in the browser's locale; it clones them and fills in dates and labels. --}}
    <template data-template="months"><div data-slot="calendar-months"></div></template>
    <template data-template="nav">
        <nav data-slot="calendar-nav">
            <x-ui::button variant="ghost" size="icon" data-calendar-previous :aria-label="__('ui::calendar.previous')"><x-ui::icon name="chevron-left" /></x-ui::button>
            <x-ui::button variant="ghost" size="icon" data-calendar-next :aria-label="__('ui::calendar.next')"><x-ui::icon name="chevron-right" /></x-ui::button>
        </nav>
    </template>
    <template data-template="month">
        <div data-slot="calendar-month">
            <div data-slot="calendar-caption"></div>
            <table data-slot="calendar-grid" role="grid">
                <thead><tr data-slot="calendar-weekdays"></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </template>
    <template data-template="caption"><span data-slot="calendar-caption-label" aria-live="polite"></span></template>
    <template data-template="dropdowns"><div data-slot="calendar-dropdowns"></div></template>
    <template data-template="dropdown-month">
        <span data-slot="calendar-dropdown">
            <select data-calendar-month aria-label="{{ __('ui::calendar.month') }}"></select>
            <span data-slot="calendar-caption-label" aria-hidden="true"><x-ui::icon name="chevron-down" /></span>
        </span>
    </template>
    <template data-template="dropdown-year">
        <span data-slot="calendar-dropdown">
            <select data-calendar-year aria-label="{{ __('ui::calendar.year') }}"></select>
            <span data-slot="calendar-caption-label" aria-hidden="true"><x-ui::icon name="chevron-down" /></span>
        </span>
    </template>
    <template data-template="option"><option></option></template>
    <template data-template="weekday"><th data-slot="calendar-weekday" scope="col"></th></template>
    <template data-template="week"><tr data-slot="calendar-week"></tr></template>
    <template data-template="day"><td data-slot="calendar-day" role="gridcell"><x-ui::button variant="ghost" size="icon" data-day tabindex="-1" /></td></template>
    <template data-template="day-hidden"><td data-slot="calendar-day" data-hidden="true"></td></template>
</eso-calendar>
