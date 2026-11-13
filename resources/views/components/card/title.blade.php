@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'card-title']) }}>{{ $slot }}</div>
