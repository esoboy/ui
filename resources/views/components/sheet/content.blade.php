@props(['side' => 'right', 'showCloseButton' => true])
@php
    if (!in_array($side, ['top', 'right', 'bottom', 'left'], true)) throw new \InvalidArgumentException(__('ui::errors.panel_side'));
@endphp
<dialog {{ $attributes->merge(['data-slot' => 'sheet-content', 'data-side' => $side, 'role' => 'dialog']) }}
    x-bind:id="$id('ui-dialog')" data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()">{{ $slot }}
    @if ($showCloseButton)
        <x-ui::sheet.close><x-ui::button variant="ghost" size="icon-sm" class="ui-sheet-dismiss" :aria-label="__('ui::ui.close')"><x-ui::icon name="x" /></x-ui::button></x-ui::sheet.close>
    @endif
</dialog>
