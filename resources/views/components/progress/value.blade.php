@aware(['value' => null, 'max' => 100])
<span {{ $attributes->class('ui-progress-value') }}>{{ $value === null ? '' : round($value / $max * 100).'%' }}</span>
