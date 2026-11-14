@blaze(fold: true)

<li {{ $attributes->merge(['data-slot' => 'pagination-item']) }}>{{ $slot }}</li>
