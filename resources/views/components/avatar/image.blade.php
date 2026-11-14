@blaze(fold: true)

{{-- Sits over the fallback; ui.js hides it when it fails to load. --}}
<img {{ $attributes->merge(['data-slot' => 'avatar-image', 'x-data' => '{ failed: false }', 'x-init' => 'failed = $el.complete && $el.naturalWidth === 0', 'x-bind:hidden' => 'failed', 'x-on:error' => 'failed = true', 'x-on:load' => 'failed = false']) }}>
