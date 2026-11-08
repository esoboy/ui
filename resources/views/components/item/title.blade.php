@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'item-title']) }}>{{ $slot }}</div>
