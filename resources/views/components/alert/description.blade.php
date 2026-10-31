@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'alert-description']) }}>{{ $slot }}</div>
