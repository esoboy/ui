@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'message-content']) }}>{{ $slot }}</div>
