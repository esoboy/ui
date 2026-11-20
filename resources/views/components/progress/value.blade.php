@aware(['value' => null, 'max' => 100])
<span {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'progress-value']) }}>{{ $value === null ? '' : round($value / $max * 100).'%' }}</span>
