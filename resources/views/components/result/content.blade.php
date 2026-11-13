@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'result-content']) }}>{{ $slot }}</div>
