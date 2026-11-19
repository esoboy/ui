@props(['size' => 'default'])
<dialog {{ $attributes->class('group/alert-dialog-content')->merge(['data-slot' => 'alert-dialog-content', 'data-size' => $size, 'role' => 'alertdialog']) }}
    x-bind:id="$id('ui-dialog')" data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()">{{ $slot }}
</dialog>
