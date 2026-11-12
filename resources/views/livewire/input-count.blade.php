<eso-input-group-count {{ $attributes->merge([
    'data-slot' => 'input-group-count',
    'data-max' => $max,
    'data-over' => $max !== null && $count > $max,
    'x-data' => 'uiInputCount',
]) }}>{{ $max !== null ? __('ui::input-group.count-max', ['count' => $count, 'max' => $max]) : __('ui::input-group.count', ['count' => $count]) }}</eso-input-group-count>
