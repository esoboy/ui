@props(['color' => null, 'value', 'initialValue', 'clearable' => false, 'seconds' => false, 'initialMonth' => null, 'min' => null, 'max' => null, 'captionLayout' => 'dropdown', 'weekStartsOn' => 0, 'showWeekNumber' => false, 'disabledDates' => [], 'fixedWeeks' => false, 'locale' => null, 'dayLabels' => [], 'dayClasses' => [], 'monthLabelFormat' => 'short', 'startMonth' => null, 'endMonth' => null])
@include('ui::partials.picker-field', [
    'picker' => 'datetime-picker',
    'options' => ['withTime' => true, 'multiple' => false, 'range' => false, 'numberOfMonths' => 1, ...compact('seconds', 'clearable', 'initialMonth', 'min', 'max', 'captionLayout', 'weekStartsOn', 'showWeekNumber', 'disabledDates', 'fixedWeeks', 'locale', 'dayLabels', 'dayClasses', 'monthLabelFormat', 'startMonth', 'endMonth')],
])
