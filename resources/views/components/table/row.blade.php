@blaze(fold: true)

<tr {{ $attributes->merge(['data-slot' => 'table-row']) }}>{{ $slot }}</tr>
