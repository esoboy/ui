@blaze
<span {{ $attributes->merge(['data-slot' => 'avatar-fallback']) }} x-show="!loaded">{{ $slot }}</span>
