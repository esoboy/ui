@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'empty-content']) }}>{{ $slot }}</div>
