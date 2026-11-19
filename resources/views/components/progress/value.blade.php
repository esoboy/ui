@aware(['value' => null, 'max' => 100])
<span {{ $attributes->merge(['data-slot' => 'progress-value']) }}>{{ $value === null ? '' : round($value / $max * 100).'%' }}</span>
