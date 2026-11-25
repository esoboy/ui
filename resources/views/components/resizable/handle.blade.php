<button type="button" role="separator"
    {{ $attributes->merge(['class' => 'ui-resizable-handle', 'aria-valuemin' => 10, 'aria-valuemax' => 90, 'aria-valuenow' => 50]) }}
    x-init="syncHandle($el)" x-bind:aria-orientation="direction === 'horizontal' ? 'vertical' : 'horizontal'"
    x-on:pointerdown.prevent="start($event)" x-on:pointermove="move($event)"
    x-on:keydown.home.prevent="resize($el, 10)" x-on:keydown.end.prevent="resize($el, 90)"
    x-on:keydown.left="nudge($event, 'horizontal', -5)" x-on:keydown.right="nudge($event, 'horizontal', 5)"
    x-on:keydown.up="nudge($event, 'vertical', -5)" x-on:keydown.down="nudge($event, 'vertical', 5)"><span></span></button>
