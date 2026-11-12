@blaze(fold: true)

<div {{ $attributes->merge(['role' => 'list', 'data-slot' => 'item-group']) }}>{{ $slot }}</div>
