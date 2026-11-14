@blaze(fold: true)

<dd {{ $attributes->merge(['data-slot' => 'description-list-details']) }}>{{ $slot }}</dd>
