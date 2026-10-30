@blaze(fold: true)

<p {{ $attributes->merge(['data-slot' => 'field-description']) }}>{{ $slot }}</p>
