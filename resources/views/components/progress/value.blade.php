@aware(['progress' => null])
<span {{ $attributes->class('ui-progress-value') }}>{{ $progress?->value === null ? '' : round($progress->value / $progress->max * 100).'%' }}</span>
