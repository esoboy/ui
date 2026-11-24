@props(['size' => 'default', 'value', 'initialValue', 'clearable' => false, 'initialMonth' => null, 'min' => null, 'max' => null, 'multiple' => false, 'range' => false, 'numberOfMonths' => 1, 'captionLayout' => 'dropdown', 'weekStartsOn' => 0, 'showWeekNumber' => false, 'disabledDates' => [], 'fixedWeeks' => false, 'locale' => null, 'dayLabels' => [], 'dayClasses' => [], 'monthLabelFormat' => 'short', 'startMonth' => null, 'endMonth' => null])
@include('ui::partials.picker-field', [
    'picker' => 'date-picker',
    'options' => ['withTime' => false, 'seconds' => false, ...compact('clearable', 'initialMonth', 'min', 'max', 'multiple', 'range', 'numberOfMonths', 'captionLayout', 'weekStartsOn', 'showWeekNumber', 'disabledDates', 'fixedWeeks', 'locale', 'dayLabels', 'dayClasses', 'monthLabelFormat', 'startMonth', 'endMonth')],
])
