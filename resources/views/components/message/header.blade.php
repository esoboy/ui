@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'message-header']) }}>{{ $slot }}</div>
