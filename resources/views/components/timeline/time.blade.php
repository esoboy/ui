@blaze(fold: true)

<time {{ $attributes->merge(['data-slot' => 'timeline-time']) }}>{{ $slot }}</time>
