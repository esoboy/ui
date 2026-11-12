@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'attachment-content']) }}>{{ $slot }}</div>
