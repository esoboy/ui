@blaze(fold: true)

<p {{ $attributes->merge(['data-slot' => 'item-description']) }}>{{ $slot }}</p>
