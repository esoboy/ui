@blaze

@use('Esoboy\Ui\Support\PaginationLinks')

@props([
    'paginator' => null,
])

{{-- With a Laravel paginator the links render themselves, and nothing renders for a single page. --}}
@if ($paginator === null || $paginator->hasPages())
<nav {{ $attributes->merge(['role' => 'navigation', 'aria-label' => __('ui::pagination.label'), 'data-slot' => 'pagination']) }}>
    @if ($paginator === null)
    {{ $slot }}
    @else
    <x-ui::pagination.content>
        @foreach (PaginationLinks::for($paginator) as $link)
        <x-ui::pagination.item>
            @if ($link['type'] === 'previous')
            <x-ui::pagination.previous :disabled="$link['disabled']" :attributes="new \Illuminate\View\ComponentAttributeBag($link['attributes'])" />
            @elseif ($link['type'] === 'next')
            <x-ui::pagination.next :disabled="$link['disabled']" :attributes="new \Illuminate\View\ComponentAttributeBag($link['attributes'])" />
            @elseif ($link['type'] === 'ellipsis')
            <x-ui::pagination.ellipsis />
            @else
            <x-ui::pagination.link :is-active="$link['active']" :attributes="new \Illuminate\View\ComponentAttributeBag($link['attributes'])">{{ $link['page'] }}</x-ui::pagination.link>
            @endif
        </x-ui::pagination.item>
        @endforeach
    </x-ui::pagination.content>
    @endif
</nav>
@endif
