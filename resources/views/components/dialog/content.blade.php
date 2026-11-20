@props(['side' => 'right', 'showCloseButton' => true])
<dialog {{ $attributes->class('ui-dialog-content')->merge(['data-esoboy-ui' => '', 'data-slot' => 'dialog-content', 'data-side' => $side, 'role' => 'dialog']) }}
    x-bind:id="$id('ui-dialog')" data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()">{{ $slot }}
    @if ($showCloseButton)
        <x-ui::dialog.close><x-ui::button variant="ghost" size="icon-sm" class="ui-dialog-dismiss" :aria-label="__('ui::ui.close')"><x-ui::icon name="x" /></x-ui::button></x-ui::dialog.close>
    @endif
</dialog>
