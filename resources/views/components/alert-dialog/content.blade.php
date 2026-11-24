@props(['status' => null, 'size' => 'default'])
<dialog {{ $attributes->class(['ui-alert-dialog-content group/alert-dialog-content', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['data-size' => $size, 'role' => 'alertdialog']) }}
    x-bind:id="$id('ui-dialog')" data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()">{{ $slot }}
</dialog>
