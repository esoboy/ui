@props(['page'])
<button type="button" data-slot="carousel-page" x-on:click="goTo({{ (int) $page }})"
    x-show="pages >= {{ (int) $page }}" x-bind:aria-current="current === {{ (int) $page }} ? 'page' : null"
    {{ $attributes->merge(['aria-label' => __('ui::ui.slide_number', ['number' => $page])]) }}>{{ $slot }}</button>
