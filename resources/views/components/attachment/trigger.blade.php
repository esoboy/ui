@blaze(fold: true, unsafe: ['href'])

{{-- Covers the whole attachment: a link with href (e.g. a download), otherwise a button. --}}
@if ($attributes->has('href'))
<a {{ $attributes->merge(['data-slot' => 'attachment-trigger']) }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'attachment-trigger']) }}>{{ $slot }}</button>
@endif
