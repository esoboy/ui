@props(['value', 'initialValue', 'seconds' => false, 'clearable' => false])
@include('ui::partials.picker-field', [
    'picker' => 'time-picker',
    'options' => compact('seconds', 'clearable'),
])
