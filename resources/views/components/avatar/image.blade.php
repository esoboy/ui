@blaze(fold: true)

{{-- Sits over the fallback; ui.js hides it when it fails to load. --}}
<img {{ $attributes->merge(['data-slot' => 'avatar-image']) }}>
