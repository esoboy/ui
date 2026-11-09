@blaze(fold: true)

<span {{ $attributes->merge(['data-slot' => 'avatar-fallback']) }}>{{ $slot }}</span>
