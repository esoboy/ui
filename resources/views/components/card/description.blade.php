@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'card-description']) }}>{{ $slot }}</div>
