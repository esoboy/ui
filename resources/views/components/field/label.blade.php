@blaze(fold: true)

<label {{ $attributes->merge(['data-slot' => 'field-label']) }}>{{ $slot }}</label>
