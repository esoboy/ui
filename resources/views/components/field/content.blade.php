@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'field-content']) }}>{{ $slot }}</div>
