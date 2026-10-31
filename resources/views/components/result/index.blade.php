@blaze

@props([
    'status' => 'info',
])

{{-- Shaped like Empty. Without content it shows the status's media, and a title and description for 403, 404 and 500. --}}
<div {{ $attributes->merge(['data-slot' => 'result', 'data-status' => $status]) }}>
    @if ($slot->isEmpty())
    <x-ui::result.header>
        <x-ui::result.media />
        <x-ui::result.title />
        <x-ui::result.description />
    </x-ui::result.header>
    @else
    {{ $slot }}
    @endif
</div>
