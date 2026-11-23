<button type="button" role="separator"
    {{ $attributes->merge(['class' => 'ui-resizable-handle', 'aria-valuemin' => 10, 'aria-valuemax' => 90, 'aria-valuenow' => 50]) }}
    x-bind:aria-orientation="direction === 'horizontal' ? 'vertical' : 'horizontal'"
    x-on:pointerdown.prevent="start($event)" x-on:keydown="key($event)"><span></span></button>
