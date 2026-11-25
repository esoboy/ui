<div {{ $attributes->whereDoesntStartWith('wire:model')->class('group/sidebar-wrapper')->merge(['class' => 'ui-sidebar ui-sidebar-variant-'.$variant, 'data-collapsible' => $collapsible, 'data-side' => $side]) }} data-ui-overlay data-ui-static-expanded
    x-data="uiSidebar(@js($collapsible))" data-state="{{ $this->current ? 'expanded' : 'collapsed' }}" x-bind:data-state="opened ? 'expanded' : 'collapsed'" x-bind:data-mobile="mobile ? 'true' : 'false'" x-id="['ui-sidebar']"
    x-on:keydown="if (!$event.defaultPrevented && $event.key === 'b' && ($event.ctrlKey || $event.metaKey)) { $event.preventDefault(); toggle() }">
    {{ $slot }}
</div>
