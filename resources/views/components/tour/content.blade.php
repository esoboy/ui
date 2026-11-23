<dialog {{ $attributes->class('ui-tour-content') }} data-ui-panel x-bind:id="$id('ui-tour')"
    x-on:close="closed()">{{ $slot }}</dialog>
