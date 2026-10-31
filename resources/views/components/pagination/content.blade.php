@blaze(fold: true)

<ul {{ $attributes->merge(['data-slot' => 'pagination-content']) }}>{{ $slot }}</ul>
