@props(['size' => 'default', 'variant' => 'default', 'side' => 'right', 'showCloseButton' => true])
<dialog {{ $attributes->class(['ui-dialog-content', 'ui-dialog-content-variant-'.$variant])->merge(['data-size' => $size, 'data-side' => $side, 'role' => 'dialog']) }}
    @unless($attributes->has('id')) x-bind:id="$id('ui-dialog')" @endunless data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()">{{ $slot }}
    @if ($showCloseButton)
        <x-ui::dialog.close><x-ui::button variant="ghost" size="icon-sm" class="ui-dialog-dismiss" :aria-label="__('ui::ui.close')"><x-ui::icon name="x" /></x-ui::button></x-ui::dialog.close>
    @endif
</dialog>
