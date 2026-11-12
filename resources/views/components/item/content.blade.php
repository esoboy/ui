@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'item-content']) }}>{{ $slot }}</div>
