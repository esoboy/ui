@blaze(fold: true)

<p {{ $attributes->merge(['data-slot' => 'anchor-title']) }}>{{ $slot }}</p>
