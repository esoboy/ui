@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'code-block-title']) }}>{{ $slot }}</div>
