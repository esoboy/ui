@blaze(fold: true)

<p {{ $attributes->merge(['data-slot' => 'empty-description']) }}>{{ $slot }}</p>
