@blaze(fold: true)

<tbody {{ $attributes->merge(['data-slot' => 'table-body']) }}>{{ $slot }}</tbody>
