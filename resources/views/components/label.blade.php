@blaze(fold: true)

<label {{ $attributes->merge(['data-slot' => 'label']) }}>{{ $slot }}</label>
