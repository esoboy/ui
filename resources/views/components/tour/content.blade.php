<dialog {{ $attributes->class('ui-tour-content') }} data-ui-panel @unless($attributes->has('id')) x-bind:id="$id('ui-tour')" @endunless
    x-on:close="closed()">{{ $slot }}</dialog>
