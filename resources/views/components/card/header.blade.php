@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'card-header']) }}>{{ $slot }}</div>
