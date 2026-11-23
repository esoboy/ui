@props(['color' => null, 'value', 'initialValue', 'initialMonth' => null, 'min' => null, 'max' => null, 'multiple' => false, 'range' => false, 'numberOfMonths' => 1, 'captionLayout' => 'label', 'weekStartsOn' => 0, 'showWeekNumber' => false, 'disabledDates' => [], 'fixedWeeks' => false, 'locale' => null, 'dayLabels' => [], 'dayClasses' => [], 'monthLabelFormat' => 'short', 'startMonth' => null, 'endMonth' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'calendar', get_defined_vars()))
<div data-esoboy-ui data-slot="calendar-field">
    {!! \Livewire\Livewire::mount(
        'ui::calendar',
        [...$binding->parameters, 'color' => $color, 'initialMonth' => $initialMonth, 'min' => $min, 'max' => $max, 'multiple' => $multiple, 'range' => $range, 'numberOfMonths' => $numberOfMonths, 'captionLayout' => $captionLayout, 'weekStartsOn' => $weekStartsOn, 'showWeekNumber' => $showWeekNumber, 'disabledDates' => $disabledDates, 'fixedWeeks' => $fixedWeeks, 'locale' => $locale, 'dayLabels' => $dayLabels, 'dayClasses' => $dayClasses, 'monthLabelFormat' => $monthLabelFormat, 'startMonth' => $startMonth, 'endMonth' => $endMonth],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="calendar-footer">{{ $footer }}</div>
    @endisset
</div>
