@blaze(fold: true)

<p {{ $attributes->merge(['data-slot' => 'popover-description']) }}>{{ $slot }}</p>
