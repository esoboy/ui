@blaze(fold: true)

{{-- Pins the Badge inside it to the top end corner of the other content, like Avatar Badge. --}}
<span {{ $attributes->merge(['data-slot' => 'badge-wrapper']) }}>{{ $slot }}</span>
