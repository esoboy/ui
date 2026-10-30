@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'empty-header']) }}>{{ $slot }}</div>
