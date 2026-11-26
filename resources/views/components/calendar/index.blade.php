@props(['size' => 'default', 'value', 'initialValue', 'initialMonth' => null, 'min' => null, 'max' => null, 'multiple' => false, 'range' => false, 'numberOfMonths' => 1, 'captionLayout' => 'label', 'weekStartsOn' => 0, 'showWeekNumber' => false, 'disabledDates' => [], 'fixedWeeks' => false, 'locale' => null, 'dayLabels' => [], 'dayClasses' => [], 'monthLabelFormat' => 'short', 'startMonth' => null, 'endMonth' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'calendar', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-calendar ui-calendar-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::calendar',
        [...$binding->parameters, 'size' => $size, 'initialMonth' => $initialMonth, 'min' => $min, 'max' => $max, 'multiple' => $multiple, 'range' => $range, 'numberOfMonths' => $numberOfMonths, 'captionLayout' => $captionLayout, 'weekStartsOn' => $weekStartsOn, 'showWeekNumber' => $showWeekNumber, 'disabledDates' => $disabledDates, 'fixedWeeks' => $fixedWeeks, 'locale' => $locale, 'dayLabels' => $dayLabels, 'dayClasses' => $dayClasses, 'monthLabelFormat' => $monthLabelFormat, 'startMonth' => $startMonth, 'endMonth' => $endMonth],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-calendar-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
