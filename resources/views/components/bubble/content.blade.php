@blaze(fold: true, unsafe: ['href', 'type'])

{{-- A link with href, a button with type (e.g. a quick reply), otherwise a div. --}}
@if ($attributes->has('href'))
<a {{ $attributes->merge(['data-slot' => 'bubble-content']) }}>{{ $slot }}</a>
@elseif ($attributes->has('type'))
<button {{ $attributes->merge(['data-slot' => 'bubble-content']) }}>{{ $slot }}</button>
@else
<div {{ $attributes->merge(['data-slot' => 'bubble-content']) }}>{{ $slot }}</div>
@endif
