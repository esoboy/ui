<div data-slot="sidebar-gap" aria-hidden="true"></div>
<dialog {{ $attributes->class('group peer')->merge(['data-slot' => 'sidebar-content']) }} data-ui-panel x-bind:id="$id('ui-sidebar')" x-bind:role="mobile ? 'dialog' : 'complementary'"
    x-bind:data-collapsible="!mobile && !opened ? $root.dataset.collapsible : null"
    x-bind:aria-modal="mobile && opened ? 'true' : null" x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()"><div data-slot="sidebar-inner">{{ $slot }}</div></dialog>
