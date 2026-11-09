@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'item-header']) }}>{{ $slot }}</div>
