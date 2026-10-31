@blaze(fold: true)

<option {{ $attributes->merge(['data-slot' => 'native-select-option']) }}>{{ $slot }}</option>
