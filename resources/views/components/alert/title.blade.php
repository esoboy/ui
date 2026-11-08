@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'alert-title']) }}>{{ $slot }}</div>
